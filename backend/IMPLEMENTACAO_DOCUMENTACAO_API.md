# 📚 Implementação da Documentação da API

## ✅ Status: Implementado

A documentação da API foi implementada usando **NelmioApiDocBundle** com **Swagger/OpenAPI 3.0**.

---

## 📦 Dependências Instaladas

- `nelmio/api-doc-bundle` (v4.6.2)
- `symfony/twig-bundle` (para Swagger UI)
- `symfony/asset` (para assets do Swagger UI)

---

## ⚙️ Configuração

### 1. Configuração do Bundle

**Arquivo:** `config/packages/nelmio_api_doc.yaml`

```yaml
nelmio_api_doc:
    documentation:
        info:
            title: POBJ API
            description: API RESTful para o sistema POBJ (Plano de Objetivos)
            version: 1.0.0
            contact:
                name: Suporte POBJ
        servers:
            - url: http://localhost:8081
              description: Servidor de desenvolvimento
            - url: https://api.exemplo.com
              description: Servidor de produção
        components:
            securitySchemes:
                ApiKeyAuth:
                    type: apiKey
                    in: header
                    name: X-API-Key
                    description: API Key única do projeto
        security:
            - ApiKeyAuth: []
    areas:
        path_patterns:
            - ^/api(?!/doc$) # Documenta todas as rotas /api exceto /api/doc
```

### 2. Rotas da Documentação

**Arquivo:** `config/routes/nelmio_api_doc.yaml`

- **JSON Swagger:** `GET /api/doc.json` - Retorna especificação OpenAPI em JSON
- **Swagger UI:** `GET /api/doc` - Interface visual interativa

---

## 📝 Endpoints Documentados

### ✅ Endpoints Principais

1. **Health Check** (`GET /api/health`)
   - Endpoint público
   - Não requer autenticação
   - Retorna status da API

2. **Resumo** (`GET /api/pobj/resumo`)
   - Retorna resumo completo de produtos
   - Parâmetros: dataInicio, dataFim, segmentoId, diretoriaId, regionalId, agenciaId, gerente, familiaId, indicadorId, status
   - Requer API Key

3. **Inicialização** (`GET /api/pobj/init`)
   - Retorna estruturas hierárquicas
   - Requer API Key

4. **Produtos** (`GET /api/produtos`)
   - Lista de produtos
   - Parâmetros: dataInicio, dataFim
   - Requer API Key

5. **Produtos Mensais** (`GET /api/produtos/mensais`)
   - Produtos agrupados por mês
   - Parâmetros: dataInicio, dataFim
   - Requer API Key

6. **Ranking** (`GET /api/pobj/ranking`)
   - Ranking de colaboradores
   - Parâmetros: dataInicio, dataFim, page, limit
   - Requer API Key

7. **Detalhes** (`GET /api/pobj/detalhes`)
   - Detalhamento de produtos
   - Parâmetros: dataInicio, dataFim, produtoId
   - Requer API Key

8. **Agent** (`POST /api/agent`)
   - Processa perguntas do agente de IA
   - Body: { "question": "string", "context": {} }
   - Requer API Key

---

## 🔍 Como Usar

### 1. Acessar a Documentação Interativa

Abra no navegador:
```
http://localhost:8081/api/doc
```

### 2. Obter JSON da Especificação

```bash
curl http://localhost:8081/api/doc.json
```

### 3. Testar Endpoints

A interface Swagger UI permite:
- Ver todos os endpoints documentados
- Testar requisições diretamente na interface
- Ver exemplos de request/response
- Autenticar com API Key

---

## 📋 Exemplo de Documentação

### Sintaxe Swagger Annotations

```php
use Swagger\Annotations as SWG;

/**
 * @Route("/api/pobj/resumo", name="api_pobj_resumo", methods={"GET"})
 * 
 * @SWG\Get(
 *     path="/api/pobj/resumo",
 *     summary="Retorna resumo de produtos",
 *     description="Retorna resumo completo com produtos, produtos mensais, variáveis e snapshot de negócio",
 *     tags={"POBJ", "Resumo"},
 *     security={{"ApiKeyAuth": {}}},
 *     @SWG\Parameter(
 *         name="dataInicio",
 *         in="query",
 *         description="Data de início do período (formato: YYYY-MM-DD)",
 *         required=false,
 *         type="string",
 *         format="date",
 *         example="2024-01-01"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="Resumo retornado com sucesso",
 *         @SWG\Schema(
 *             type="object",
 *             @SWG\Property(property="success", type="boolean", example=true),
 *             @SWG\Property(property="data", type="object")
 *         )
 *     ),
 *     @SWG\Response(
 *         response=401,
 *         description="Não autorizado",
 *         @SWG\Schema(
 *             type="object",
 *             @SWG\Property(property="success", type="boolean", example=false),
 *             @SWG\Property(property="data", type="object",
 *                 @SWG\Property(property="error", type="string", example="API Key inválida"),
 *                 @SWG\Property(property="code", type="string", example="UNAUTHORIZED")
 *             )
 *         )
 *     )
 * )
 */
```

---

## 🔒 Códigos de Erro Documentados

### Respostas Padrão

Todos os endpoints documentam os seguintes códigos de erro:

- **200** - Sucesso
- **400** - Bad Request (requisição inválida)
- **401** - Unauthorized (API Key inválida ou ausente)
- **404** - Not Found (recurso não encontrado)
- **422** - Unprocessable Entity (erro de validação)
- **429** - Too Many Requests (rate limit excedido)
- **500** - Internal Server Error (erro interno)

### Formato de Resposta de Erro

```json
{
  "success": false,
  "data": {
    "error": "Mensagem de erro",
    "code": "ERROR_CODE",
    "details": {},
    "timestamp": "2024-12-03T22:00:00+00:00",
    "request_id": "uuid-do-request"
  }
}
```

---

## 📁 Arquivos Modificados

### Controllers Documentados

- ✅ `src/Controller/HealthController.php`
- ✅ `src/Controller/Pobj/ResumoController.php`
- ✅ `src/Controller/Pobj/InitController.php`
- ✅ `src/Controller/Pobj/ProdutosController.php`
- ✅ `src/Controller/Pobj/RankingController.php`
- ✅ `src/Controller/Pobj/DetalhesController.php`
- ✅ `src/Controller/Pobj/AgentController.php`

### Configuração

- ✅ `config/packages/nelmio_api_doc.yaml`
- ✅ `config/routes/nelmio_api_doc.yaml`

---

## 🚀 Próximos Passos

### Endpoints Documentados

- [x] `GET /api/pobj/calendario` - CalendarioController ✅
- [x] `GET /api/pobj/simulador` - SimuladorController ✅
- [x] `GET /api/pobj/exec` - ExecController ✅
- [x] `POST /api/pobj/notifications` - PobjNotificationsController ✅
- [x] `GET /api/omega/init` - OmegaInitController ✅
- [x] `GET /api/omega/users` - OmegaUsersController ✅
- [x] `GET /api/omega/tickets` - OmegaTicketsController ✅
- [x] `POST /api/omega/tickets` - OmegaTicketsController ✅
- [x] `PUT /api/omega/tickets/{id}` - OmegaTicketsController ✅
- [x] `GET /api/omega/teams/{supervisorId}/analysts` - OmegaTeamsController ✅
- [x] `POST /api/omega/teams/{supervisorId}/analysts` - OmegaTeamsController ✅
- [x] `POST /api/omega/teams/{supervisorId}/analysts/{analystId}/remove` - OmegaTeamsController ✅
- [x] `GET /api/omega/analysts/available` - OmegaTeamsController ✅
- [x] `POST /api/omega/notifications` - OmegaNotificationsController ✅
- [x] `GET /api/omega/statuses` - OmegaStatusController ✅
- [x] `GET /api/omega/structure` - OmegaStructureController ✅
- [x] `GET /api/omega/mesu` - OmegaMesuController ✅

### Melhorias Futuras

- [ ] Adicionar exemplos mais detalhados de request/response
- [ ] Documentar schemas de dados complexos
- [ ] Adicionar mais tags para organização
- [ ] Documentar rate limits específicos por endpoint
- [ ] Adicionar diagramas de fluxo

---

## ✅ Checklist de Implementação

- [x] Instalar NelmioApiDocBundle
- [x] Configurar bundle
- [x] Habilitar Swagger UI
- [x] Documentar endpoints principais
- [x] Adicionar exemplos de request/response
- [x] Documentar códigos de erro
- [x] Configurar autenticação (API Key)
- [x] Testar documentação

---

## 📚 Recursos

- [NelmioApiDocBundle Documentation](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html)
- [Swagger/OpenAPI Specification](https://swagger.io/specification/)
- [Swagger Annotations](https://github.com/zircote/swagger-php)

---

**Status:** ✅ Implementação Completa
**Data:** 2024-12-03
**Versão:** 1.0.0

