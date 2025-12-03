# 📚 Guia de Uso - Tratamento de Erros Centralizado

## 🎯 Visão Geral

O sistema de tratamento de erros foi implementado para padronizar todas as respostas de erro e facilitar o debug. Todas as exceções são capturadas automaticamente pelo `ExceptionSubscriber` e transformadas em respostas JSON padronizadas.

---

## 🚀 Como Usar

### 1. Lançando Exceptions Customizadas

#### BadRequestException - Requisição Inválida (400)
```php
use App\Exception\BadRequestException;

// No controller ou use case
throw new BadRequestException('Payload inválido');
throw new BadRequestException('Parâmetro obrigatório ausente', ['param' => 'id']);
```

#### ValidationException - Erro de Validação (422)
```php
use App\Exception\ValidationException;

$errors = [
    'email' => 'Email inválido',
    'password' => 'Senha deve ter no mínimo 8 caracteres'
];
throw new ValidationException('Dados de entrada inválidos', $errors);
```

#### NotFoundException - Recurso Não Encontrado (404)
```php
use App\Exception\NotFoundException;

throw new NotFoundException('Produto não encontrado', 'produto');
throw new NotFoundException('Usuário não encontrado', 'usuario');
```

#### UnauthorizedException - Não Autorizado (401)
```php
use App\Exception\UnauthorizedException;

throw new UnauthorizedException('Token inválido ou expirado');
```

#### ForbiddenException - Acesso Negado (403)
```php
use App\Exception\ForbiddenException;

throw new ForbiddenException('Você não tem permissão para acessar este recurso');
```

#### DatabaseException - Erro de Banco de Dados (500)
```php
use App\Exception\DatabaseException;

try {
    // operação de banco
} catch (\Doctrine\DBAL\Exception $e) {
    throw new DatabaseException('Erro ao salvar dados', $e);
}
```

---

### 2. Usando Helpers no ControllerBase

```php
class MeuController extends ControllerBase
{
    public function exemplo(Request $request)
    {
        // Validação rápida
        if (empty($request->get('id'))) {
            $this->throwBadRequest('ID é obrigatório');
        }

        // Recurso não encontrado
        if (!$recurso) {
            $this->throwNotFound('Recurso não encontrado', 'recurso');
        }

        // Validação com múltiplos erros
        $errors = [];
        if (empty($request->get('nome'))) {
            $errors['nome'] = 'Nome é obrigatório';
        }
        if (empty($request->get('email'))) {
            $errors['email'] = 'Email é obrigatório';
        }
        if (!empty($errors)) {
            $this->throwValidationError('Dados inválidos', $errors);
        }
    }
}
```

---

### 3. Exemplo Completo de Controller

```php
<?php

namespace App\Controller\Pobj;

use App\Controller\ControllerBase;
use App\Exception\BadRequestException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\Pobj\ProdutoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProdutoController extends ControllerBase
{
    private $produtoRepository;

    public function __construct(ProdutoRepository $produtoRepository)
    {
        $this->produtoRepository = $produtoRepository;
    }

    /** @Route("/api/produto/{id}", methods={"GET"}) */
    public function getById(Request $request, string $id)
    {
        // Validação de entrada
        if (empty($id) || !is_numeric($id)) {
            throw new BadRequestException('ID inválido');
        }

        // Busca o produto
        $produto = $this->produtoRepository->find($id);

        if (!$produto) {
            throw new NotFoundException('Produto não encontrado', 'produto');
        }

        return $this->success($produto);
    }

    /** @Route("/api/produto", methods={"POST"}) */
    public function create(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new BadRequestException('Payload inválido. Esperado JSON.');
        }

        // Validação de campos
        $errors = [];
        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório';
        }
        if (empty($data['preco']) || !is_numeric($data['preco'])) {
            $errors['preco'] = 'Preço deve ser um número válido';
        }

        if (!empty($errors)) {
            throw new ValidationException('Dados de entrada inválidos', $errors);
        }

        // Processa criação...
        $produto = $this->produtoRepository->create($data);

        return $this->success($produto);
    }
}
```

---

## 📋 Formato de Resposta Padronizado

### Sucesso
```json
{
  "success": true,
  "data": {
    // dados da resposta
  }
}
```

### Erro (Produção)
```json
{
  "success": false,
  "data": {
    "error": "Mensagem de erro amigável",
    "code": "ERROR_CODE",
    "details": {
      // detalhes adicionais (ex: validation_errors)
    },
    "timestamp": "2024-01-15T10:30:00+00:00",
    "request_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

### Erro (Desenvolvimento)
```json
{
  "success": false,
  "data": {
    "error": "Mensagem de erro completa",
    "code": "ERROR_CODE",
    "details": {},
    "timestamp": "2024-01-15T10:30:00+00:00",
    "request_id": "550e8400-e29b-41d4-a716-446655440000",
    "debug": {
      "class": "App\\Exception\\NotFoundException",
      "file": "/path/to/file.php",
      "line": 42,
      "trace": [
        {
          "file": "/path/to/file.php",
          "line": 42,
          "function": "methodName",
          "class": "ClassName"
        }
      ]
    }
  }
}
```

---

## 🔍 Logging Automático

Todas as exceções são automaticamente logadas com contexto estruturado:

- **Erros 5xx**: Logados como `ERROR`
- **Erros 4xx**: Logados como `WARNING`
- **Outros**: Logados como `INFO`

### Informações Logadas:
- Mensagem da exceção
- Stack trace
- Request URI
- Request method
- Request headers
- Request content
- Error code (se AppException)
- Details (se AppException)

### Arquivos de Log:
- `var/log/{environment}.log` - Todos os logs
- `var/log/{environment}.error.log` - Apenas erros
- `var/log/{environment}.security.log` - Logs de segurança
- `var/log/{environment}.database.log` - Logs de banco de dados

---

## 🎨 Exemplos de Códigos de Erro

| Código | Descrição | HTTP Status |
|--------|-----------|-------------|
| `BAD_REQUEST` | Requisição malformada | 400 |
| `UNAUTHORIZED` | Não autenticado | 401 |
| `FORBIDDEN` | Sem permissão | 403 |
| `NOT_FOUND` | Recurso não encontrado | 404 |
| `VALIDATION_ERROR` | Erro de validação | 422 |
| `DATABASE_ERROR` | Erro de banco de dados | 500 |
| `INTERNAL_ERROR` | Erro interno genérico | 500 |

---

## 🔧 Migração de Código Existente

### Antes:
```php
if (!$produto) {
    return $this->error('Produto não encontrado', 404);
}
```

### Depois:
```php
if (!$produto) {
    throw new NotFoundException('Produto não encontrado', 'produto');
}
```

### Antes:
```php
try {
    // código
} catch (\Exception $e) {
    return $this->error($e->getMessage(), 500);
}
```

### Depois:
```php
// Apenas lance a exceção, o ExceptionSubscriber cuida do resto
// Ou encapsule em uma exception customizada:
try {
    // código
} catch (\Doctrine\DBAL\Exception $e) {
    throw new DatabaseException('Erro ao salvar', $e);
}
```

---

## 📝 Boas Práticas

1. **Use exceptions específicas**: Prefira `NotFoundException` em vez de `BadRequestException` para recursos não encontrados
2. **Mensagens claras**: Use mensagens de erro que ajudem o desenvolvedor/frontend
3. **Detalhes úteis**: Adicione detalhes relevantes no array `$details`
4. **Encapsule exceções**: Quando capturar exceções de bibliotecas, encapsule em exceptions customizadas
5. **Não retorne erros manualmente**: Deixe o ExceptionSubscriber fazer isso
6. **Use Request ID**: O Request ID é gerado automaticamente e ajuda no rastreamento

---

## 🐛 Debug

### Em Desenvolvimento:
- Stack trace completo é incluído na resposta
- Informações de arquivo e linha
- Logs detalhados

### Em Produção:
- Apenas mensagem de erro amigável
- Sem stack trace na resposta
- Logs completos no arquivo de log

---

## 📚 Referências

- [ExceptionSubscriber](../src/EventSubscriber/ExceptionSubscriber.php)
- [AppException](../src/Exception/AppException.php)
- [ControllerBase](../src/Controller/ControllerBase.php)

