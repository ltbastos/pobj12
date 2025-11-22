<?php

namespace App\Application\UseCase;

use App\Infrastructure\Helpers\KnowledgeHelper;

class AgentUseCase
{
    public function processQuestion(array $payload): array
    {
        $question = trim((string) (isset($payload['question']) ? $payload['question'] : ''));
        if ($question === '') {
            throw new \InvalidArgumentException('Campo "question" é obrigatório.');
        }

        $apiKey = KnowledgeHelper::env('OPENAI_API_KEY', '');
        if ($apiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY não configurada');
        }

        $model = KnowledgeHelper::env('OPENAI_MODEL', 'gpt-5-mini');

        $dir = __DIR__ . '/../../../../docs/knowledge';
        $indexPath = __DIR__ . '/../../../../docs/knowledge.index.json';
        $index = KnowledgeHelper::buildOrLoadIndex($dir, $indexPath);

        $top = KnowledgeHelper::retrieveTopK($question, $index, 6);
        $context = $this->buildContext($top);
        $sources = $this->buildSources($top);

        $userName = $this->extractUserName($payload);

        $system = $this->getSystemPrompt();
        $user = $this->buildUserPrompt($userName, $question, $context);

        $supportsTemp = !preg_match('/\b(gpt-5-mini|gpt-5-nano)\b/i', $model);
        $payloadOpenAI = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];

        if ($supportsTemp) {
            $payloadOpenAI['temperature'] = 0.2;
        }

        $resp = KnowledgeHelper::httpPostJson(
            'https://api.openai.com/v1/chat/completions',
            ['Authorization: Bearer ' . $apiKey],
            $payloadOpenAI
        );

        $answer = trim((string) (isset($resp['choices'][0]['message']['content']) ? $resp['choices'][0]['message']['content'] : ''));

        return [
            'answer' => $answer,
            'sources' => $sources,
            'model' => $model,
        ];
    }

    private function buildContext(array $top): string
    {
        $context = '';
        foreach ($top as $i => $hit) {
            $n = $i + 1;
            $context .= "[$n] (" . $hit['name'] . " #" . $hit['chunk_id'] . ")\n" . $hit['text'] . "\n\n";
        }

        return $context !== '' ? $context : "Nenhum documento disponível em docs/knowledge.";
    }

    private function buildSources(array $top): array
    {
        $sources = [];
        foreach ($top as $i => $hit) {
            $sources[] = [
                'rank' => $i + 1,
                'file' => $hit['name'],
                'path' => $hit['source'],
                'chunk' => $hit['chunk_id'],
                'score' => round($hit['score'], 4),
            ];
        }
        return $sources;
    }

    private function extractUserName(array $payload): string
    {
        if (empty($payload['user_name'])) {
            return '';
        }

        $parts = preg_split('/\s+/', trim((string) $payload['user_name']));
        return $parts ? $parts[0] : '';
    }

    private function getSystemPrompt(): string
    {
        return <<<SYS
Você é o **Assistente POBJ & Campanhas** para agências no Brasil.
REGRAS OBRIGATÓRIAS:
1) ESCOPO FECHADO: responda **somente** com base no conteúdo dos manuais do **POBJ** e de **Campanhas** fornecidos no *Contexto*. 
   • Se a pergunta estiver fora do escopo ou o contexto não trouxer evidência suficiente, responda **em 1 linha**:
     "Posso ajudar apenas com o POBJ e as Campanhas. Isso não está no manual. 🙂"
2) ESTILO: seja **direto ao ponto**, **nada verboso**. No máximo **2–3 frases curtas** ou **até 3 bullets** (curtos).
   • Tom simpático e animado; use **1 emoji** pertinente (no início ou fim). Evite vários emojis.
3) CITAÇÕES: quando afirmar uma regra/dado, referencie o trecho como **[arquivo #chunk]** quando isso ajudar.
4) AMBIGUIDADE: se faltar um detalhe essencial, faça **no máximo 1** pergunta de esclarecimento em 1 linha.
5) PERSONALIZAÇÃO: se for informado "Usuário: NOME", **cumprimente pelo primeiro nome** no início (ex.: "Oi, Ana!").
6) Português do Brasil, claro e profissional.
SYS;
    }

    private function buildUserPrompt(string $userName, string $question, string $context): string
    {
        $saud = $userName ? "Usuário: {$userName}" : "Usuário: (não informado)";
        return "{$saud}\n" .
            "Pergunta: {$question}\n\n" .
            "Contexto (trechos recuperados dos manuais):\n{$context}";
    }
}

