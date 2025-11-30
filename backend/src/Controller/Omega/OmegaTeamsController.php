<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaUsersUseCase;
use App\Controller\ControllerBase;
use App\Infrastructure\Database\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller para gerenciar times de supervisão do Omega
 */
class OmegaTeamsController extends ControllerBase
{
    private $omegaUsersUseCase;

    public function __construct(OmegaUsersUseCase $omegaUsersUseCase)
    {
        $this->omegaUsersUseCase = $omegaUsersUseCase;
    }

    /**
     * Lista analistas do time de um supervisor
     * @Route("/api/omega/teams/{supervisorId}/analysts", name="api_omega_teams_analysts", methods={"GET"})
     */
    public function getTeamAnalysts(string $supervisorId, Request $request): JsonResponse
    {
        try {
            // Busca analistas que pertencem ao time do supervisor
            // Usa uma tabela de relacionamento omega_team_analysts (supervisor_id, analyst_id)
            $connection = Connection::getInstance();
            $pdo = $connection->getPdo();
            
            $sql = "SELECT u.* FROM omega_usuarios u
                    INNER JOIN omega_team_analysts ta ON u.id = ta.analyst_id
                    WHERE ta.supervisor_id = ? AND u.analista = 1
                    ORDER BY u.nome";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$supervisorId]);
            $analysts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Se a tabela não existir, retorna array vazio
            if ($analysts === false) {
                return $this->success([]);
            }

            // Converte para formato esperado pelo frontend
            $result = array_map(function($analyst) {
                return [
                    'id' => $analyst['id'],
                    'name' => $analyst['nome'],
                    'functional' => $analyst['funcional'],
                    'role' => 'analista',
                    'analista' => (bool)$analyst['analista'],
                    'supervisor' => (bool)$analyst['supervisor'],
                    'admin' => (bool)$analyst['admin']
                ];
            }, $analysts);

            return $this->success($result);
        } catch (\Exception $e) {
            // Se a tabela não existir, retorna array vazio
            error_log('Erro ao buscar analistas do time: ' . $e->getMessage());
            return $this->success([]);
        }
    }

    /**
     * Adiciona um analista ao time de um supervisor
     * @Route("/api/omega/teams/{supervisorId}/analysts", name="api_omega_teams_add_analyst", methods={"POST"})
     */
    public function addAnalystToTeam(string $supervisorId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['analystId'])) {
            return $this->error('ID do analista é obrigatório', 400);
        }

        $analystId = $data['analystId'];

        try {
            // Verifica se o supervisor existe e é supervisor
            $connection = Connection::getInstance();
            $pdo = $connection->getPdo();
            
            $supervisorSql = "SELECT * FROM omega_usuarios WHERE id = ? AND supervisor = 1";
            $stmt = $pdo->prepare($supervisorSql);
            $stmt->execute([$supervisorId]);
            $supervisor = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$supervisor) {
                return $this->error('Supervisor não encontrado ou não tem permissão', 404);
            }

            // Verifica se o analista existe e é analista
            $analystSql = "SELECT * FROM omega_usuarios WHERE id = ? AND analista = 1";
            $stmt = $pdo->prepare($analystSql);
            $stmt->execute([$analystId]);
            $analyst = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$analyst) {
                return $this->error('Analista não encontrado', 404);
            }

            // Verifica se já existe o relacionamento
            $checkSql = "SELECT * FROM omega_team_analysts WHERE supervisor_id = ? AND analyst_id = ?";
            $stmt = $pdo->prepare($checkSql);
            $stmt->execute([$supervisorId, $analystId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                return $this->error('Analista já está no time deste supervisor', 400);
            }

            // Insere o relacionamento
            $insertSql = "INSERT INTO omega_team_analysts (supervisor_id, analyst_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([$supervisorId, $analystId]);

            return $this->success([
                'message' => 'Analista adicionado ao time com sucesso',
                'supervisorId' => $supervisorId,
                'analystId' => $analystId
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao adicionar analista ao time: ' . $e->getMessage());
            return $this->error('Erro ao adicionar analista ao time: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove um analista do time de um supervisor
     * @Route("/api/omega/teams/{supervisorId}/analysts/{analystId}/remove", name="api_omega_teams_remove_analyst", methods={"POST"})
     */
    public function removeAnalystFromTeam(string $supervisorId, string $analystId): JsonResponse
    {
        try {
            // Remove o relacionamento
            $connection = Connection::getInstance();
            $pdo = $connection->getPdo();
            
            $sql = "DELETE FROM omega_team_analysts WHERE supervisor_id = ? AND analyst_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$supervisorId, $analystId]);

            if ($stmt->rowCount() === 0) {
                return $this->error('Relacionamento não encontrado', 404);
            }

            return $this->success([
                'message' => 'Analista removido do time com sucesso',
                'supervisorId' => $supervisorId,
                'analystId' => $analystId
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao remover analista do time: ' . $e->getMessage());
            return $this->error('Erro ao remover analista do time: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lista analistas disponíveis para adicionar ao time
     * @Route("/api/omega/analysts/available", name="api_omega_analysts_available", methods={"GET"})
     */
    public function getAvailableAnalysts(Request $request): JsonResponse
    {
        try {
            $excludeTeamId = $request->query->get('excludeTeamId');

            // Busca todos os analistas
            $connection = Connection::getInstance();
            $pdo = $connection->getPdo();
            
            $sql = "SELECT * FROM omega_usuarios WHERE analista = 1 ORDER BY nome";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $allAnalysts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Se há um supervisor específico, exclui os que já estão no time dele
            if ($excludeTeamId) {
                $excludeSql = "SELECT analyst_id FROM omega_team_analysts WHERE supervisor_id = ?";
                $stmt = $pdo->prepare($excludeSql);
                $stmt->execute([$excludeTeamId]);
                $excludedIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                $allAnalysts = array_filter($allAnalysts, function($analyst) use ($excludedIds) {
                    return !in_array($analyst['id'], $excludedIds);
                });
            }

            // Converte para formato esperado pelo frontend
            $result = array_map(function($analyst) {
                return [
                    'id' => $analyst['id'],
                    'name' => $analyst['nome'],
                    'functional' => $analyst['funcional'],
                    'role' => 'analista',
                    'analista' => (bool)$analyst['analista'],
                    'supervisor' => (bool)$analyst['supervisor'],
                    'admin' => (bool)$analyst['admin']
                ];
            }, $allAnalysts);

            return $this->success(array_values($result));
        } catch (\Exception $e) {
            error_log('Erro ao buscar analistas disponíveis: ' . $e->getMessage());
            return $this->success([]);
        }
    }
}

