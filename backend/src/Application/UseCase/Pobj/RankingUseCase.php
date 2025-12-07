<?php

namespace App\Application\UseCase\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Repository\Pobj\FHistoricoRankingPobjRepository;

class RankingUseCase
{
    private $repository;

    public function __construct(FHistoricoRankingPobjRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(?FilterDTO $filters = null): array
    {
        $rawData = $this->repository->findRankingWithFilters($filters);
        
        if (empty($rawData)) {
            return [];
        }

                $nivel = $filters ? $filters->get('nivel', 'gerenteGestao') : 'gerenteGestao';
        
                return $this->processRankingData($rawData, $nivel, $filters);
    }

    
    private function processRankingData(array $rawData, string $nivel, ?FilterDTO $filters): array
    {
                $levelFields = [
            'segmento' => ['key' => 'segmento_id', 'label' => 'segmento'],
            'diretoria' => ['key' => 'diretoria_id', 'label' => 'diretoria_nome'],
            'gerencia' => ['key' => 'gerencia_id', 'label' => 'gerencia_nome'],
            'agencia' => ['key' => 'agencia_id', 'label' => 'agencia_nome'],
            'gerenteGestao' => ['key' => 'gerente_gestao_id', 'label' => 'gerente_gestao_nome'],
            'gerente' => ['key' => 'gerente_id', 'label' => 'gerente_nome'],
        ];

        $fieldConfig = $levelFields[$nivel] ?? $levelFields['gerenteGestao'];
        $keyField = $fieldConfig['key'];
        $labelField = $fieldConfig['label'];

                $selectionForLevel = $this->getSelectionForLevel($nivel, $filters);
        $hasSelection = $selectionForLevel !== null && !$this->isDefaultSelection($selectionForLevel);

                $groups = [];
        foreach ($rawData as $item) {
            // Obtém o valor da chave para o nível selecionado
            $keyValue = $item[$keyField] ?? null;
            
            // Normaliza valores NULL (pode vir como null, string vazia, ou string 'NULL')
            $isNull = ($keyValue === null || 
                      $keyValue === '' || 
                      (is_string($keyValue) && strtoupper(trim($keyValue)) === 'NULL'));
            
            // Se o campo necessário for NULL, tenta alternativas baseadas no nível
            if ($isNull) {
                if ($nivel === 'gerenteGestao') {
                    // Para gerenteGestao, se gerente_gestao_id está NULL, tenta usar gerente_gestao_id_num
                    // Isso acontece quando o cargo é GERENTE mas não tem ggestao associado
                    $idNum = $item['gerente_gestao_id_num'] ?? null;
                    if ($idNum !== null && $idNum !== '' && strtoupper(trim((string)$idNum)) !== 'NULL') {
                        $keyValue = $idNum;
                    } else {
                        // Se não tem nem gerente_gestao_id nem gerente_gestao_id_num, pula o registro
                        // Isso significa que o funcionário não é GERENTE nem GERENTE_GESTAO
                        continue;
                    }
                } else {
                    // Para outros níveis, pula se não tiver o campo necessário
                    continue;
                }
            }
            
            $key = (string)$keyValue;
            $label = $item[$labelField] ?? null;
            
            // Normaliza label NULL
            $isLabelNull = ($label === null || 
                           $label === '' || 
                           (is_string($label) && strtoupper(trim($label)) === 'NULL'));
            
            // Se não houver label, tenta usar alternativas baseadas no nível
            if ($isLabelNull) {
                if ($nivel === 'gerenteGestao') {
                    // Tenta usar gerente_gestao_nome
                    $label = $item['gerente_gestao_nome'] ?? null;
                    // Se ainda não tiver e usamos id_num como chave, usa a chave como label
                    if (($label === null || $label === '') && $keyValue === ($item['gerente_gestao_id_num'] ?? null)) {
                        $label = $key;
                    }
                    // Último fallback
                    if ($label === null || $label === '') {
                        $label = $key;
                    }
                } elseif ($nivel === 'gerente') {
                    $label = $item['gerente_nome'] ?? $item['nome'] ?? $key;
                } else {
                    $label = $key;
                }
            }
            
            // Garante que o label não seja vazio
            if ($label === '' || $label === null) {
                $label = $key ?? '—';
            }
            
            $idNum = null;
            if ($nivel === 'gerenteGestao' && isset($item['gerente_gestao_id_num']) && $item['gerente_gestao_id_num'] !== null) {
                $idNumValue = $item['gerente_gestao_id_num'];
                if ($idNumValue !== '' && strtoupper(trim((string)$idNumValue)) !== 'NULL') {
                    $idNum = (string)$idNumValue;
                }
            } elseif ($nivel === 'gerente' && isset($item['gerente_id']) && $item['gerente_id'] !== null) {
                $idNumValue = $item['gerente_id'];
                if ($idNumValue !== '' && strtoupper(trim((string)$idNumValue)) !== 'NULL') {
                    $idNum = (string)$idNumValue;
                }
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'unidade' => $key,
                    'label' => $label,
                    'id_num' => $idNum,                     'pontos' => 0,
                    'count' => 0,
                ];
            }

                        $pontos = $item['pontos'] ?? $item['realizado_mensal'] ?? 0;
            $groups[$key]['pontos'] += $pontos;
            $groups[$key]['count'] += 1;
        }

                $grouped = array_values($groups);
        usort($grouped, function($a, $b) {
            return $b['pontos'] <=> $a['pontos'];
        });

                $result = [];
        foreach ($grouped as $index => $item) {
            $shouldMask = $this->shouldMaskItem($item, $index, $hasSelection, $selectionForLevel, $nivel);
            
            $result[] = [
                'unidade' => $item['unidade'],
                'label' => $item['label'],
                'displayLabel' => $shouldMask ? '*****' : $item['label'],
                'pontos' => $item['pontos'],
                'count' => $item['count'],
                'position' => $index + 1,
            ];
        }

        return $result;
    }

    
    private function shouldMaskItem(array $item, int $index, bool $hasSelection, ?string $selectionForLevel, string $nivel): bool
    {
                if (!$hasSelection) {
            return $index !== 0;
        }

                if ($hasSelection && $selectionForLevel) {
                        $idNum = $item['id_num'] ?? null;
            $matches = $this->matchesSelection($selectionForLevel, $item['unidade'], $item['label'], $idNum);
            return !$matches;         }

        return true;
    }

    
    private function getSelectionForLevel(string $nivel, ?FilterDTO $filters): ?string
    {
        if (!$filters) {
            return null;
        }

        $levelMap = [
            'segmento' => 'segmento',
            'diretoria' => 'diretoria',
            'gerencia' => 'regional',
            'agencia' => 'agencia',
            'gerenteGestao' => 'gerenteGestao',
            'gerente' => 'gerente',
        ];

        $filterKey = $levelMap[$nivel] ?? null;
        if (!$filterKey) {
            return null;
        }

        $filterValue = $filters->get($filterKey);
        
        // Se o nível for "gerente" e o filtro for numérico, converte ID para funcional
        if ($nivel === 'gerente' && $filterValue && is_numeric($filterValue)) {
            $funcional = $this->repository->getFuncionalFromIdOrFuncional($filterValue, Cargo::GERENTE);
            if ($funcional) {
                return $funcional;
            }
        }
        
        return $filterValue;
    }

    
    private function isDefaultSelection(?string $value): bool
    {
        if (!$value) {
            return true;
        }
        $normalized = strtolower(trim($value));
        return in_array($normalized, ['todos', 'todas', '']);
    }

    
    private function matchesSelection(string $filterValue, ?string $candidate1, ?string $candidate2, ?string $candidateIdNum = null): bool
    {
        if ($this->isDefaultSelection($filterValue)) {
            return true;
        }

        $normalizedFilter = strtolower(trim($filterValue));
        
        // Comparação numérica: se o filtro for numérico, compara com ID numérico
        if (is_numeric($filterValue) && $candidateIdNum !== null) {
            $normalizedIdNum = strtolower(trim((string)$candidateIdNum));
            if ($normalizedIdNum === $normalizedFilter) {
                return true;
            }
            // Também compara diretamente os valores numéricos
            if ((string)$candidateIdNum === (string)$filterValue) {
                return true;
            }
        }
        
        // Comparação com todos os candidatos (ID, nome, ID numérico)
        $candidates = array_filter([$candidate1, $candidate2, $candidateIdNum]);
        foreach ($candidates as $candidate) {
            if (!$candidate) {
                continue;
            }
            // Comparação exata (case-insensitive)
            $normalizedCandidate = strtolower(trim((string)$candidate));
            if ($normalizedCandidate === $normalizedFilter) {
                return true;
            }
            // Comparação simplificada (remove acentos e caracteres especiais)
            $simplifiedFilter = $this->simplifyText($filterValue);
            $simplifiedCandidate = $this->simplifyText((string)$candidate);
            if ($simplifiedCandidate === $simplifiedFilter) {
                return true;
            }
        }

        return false;
    }

    
    private function simplifyText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        
                if (function_exists('iconv')) {
            $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        } else {
                        $text = str_replace(
                ['á', 'à', 'ã', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'õ', 'ô', 'ö', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ'],
                ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n'],
                $text
            );
        }
        
        $text = preg_replace('/[^a-z0-9]/', '', $text);
        return trim($text);
    }
}

