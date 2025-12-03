# ✅ Implementação - Tratamento de Erros Centralizado

## 📦 O que foi implementado

### 1. Exceptions Customizadas por Domínio ✅

Criadas exceptions específicas para diferentes tipos de erro:

- **`AppException`** - Classe base para todas as exceptions customizadas
- **`BadRequestException`** - Requisições inválidas (400)
- **`ValidationException`** - Erros de validação (422)
- **`NotFoundException`** - Recursos não encontrados (404)
- **`UnauthorizedException`** - Não autenticado (401)
- **`ForbiddenException`** - Sem permissão (403)
- **`DatabaseException`** - Erros de banco de dados (500)

**Localização:** `src/Exception/`

---

### 2. ExceptionSubscriber Global ✅

Subscriber que captura automaticamente todas as exceções e:
- Padroniza respostas de erro
- Loga exceções com contexto estruturado
- Adiciona stack trace em desenvolvimento
- Inclui Request ID para rastreamento

**Localização:** `src/EventSubscriber/ExceptionSubscriber.php`

---

### 3. RequestIdSubscriber ✅

Gera um ID único para cada requisição, facilitando rastreamento em logs.

**Localização:** `src/EventSubscriber/RequestIdSubscriber.php`

---

### 4. ControllerBase Melhorado ✅

Adicionados métodos helper para lançar exceptions:
- `throwValidationError()`
- `throwNotFound()`
- `throwBadRequest()`

**Localização:** `src/Controller/ControllerBase.php`

---

### 5. Logging Estruturado com Monolog ✅

Configurado Monolog com:
- Rotação automática de logs
- Canais separados (app, security, database, request)
- Formato JSON para logs estruturados
- Diferentes níveis de log por tipo de erro

**Localização:** `config/packages/monolog.yaml`

---

### 6. Configuração de Serviços ✅

- ExceptionSubscriber configurado no `services.yaml`
- MonologBundle adicionado ao `bundles.php`
- Dependências adicionadas ao `composer.json`

---

## 🚀 Como Instalar

### 1. Instalar Dependências

```bash
cd Backend
composer require monolog/monolog symfony/monolog-bundle
composer update
```

### 2. Limpar Cache

```bash
php bin/console cache:clear
```

### 3. Verificar Logs

Os logs serão criados automaticamente em:
- `var/log/dev.log` (desenvolvimento)
- `var/log/prod.log` (produção)
- `var/log/{env}.error.log` (apenas erros)
- `var/log/{env}.security.log` (segurança)
- `var/log/{env}.database.log` (banco de dados)

---

## 📝 Exemplo de Uso

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

O `ExceptionSubscriber` automaticamente:
- Captura a exceção
- Cria resposta JSON padronizada
- Loga com contexto completo
- Adiciona stack trace (em dev)

---

## 📋 Formato de Resposta

### Sucesso:
```json
{
  "success": true,
  "data": { ... }
}
```

### Erro (Produção):
```json
{
  "success": false,
  "data": {
    "error": "Mensagem de erro",
    "code": "NOT_FOUND",
    "details": {},
    "timestamp": "2024-01-15T10:30:00+00:00",
    "request_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

### Erro (Desenvolvimento):
```json
{
  "success": false,
  "data": {
    "error": "Mensagem de erro",
    "code": "NOT_FOUND",
    "details": {},
    "timestamp": "2024-01-15T10:30:00+00:00",
    "request_id": "550e8400-e29b-41d4-a716-446655440000",
    "debug": {
      "class": "App\\Exception\\NotFoundException",
      "file": "/path/to/file.php",
      "line": 42,
      "trace": [ ... ]
    }
  }
}
```

---

## 🔍 Logging Automático

Todas as exceções são logadas automaticamente com:

- **Erros 5xx**: Logados como `ERROR`
- **Erros 4xx**: Logados como `WARNING`
- **Contexto completo**: URI, método, headers, conteúdo, stack trace

### Exemplo de Log:
```
[2024-01-15 10:30:00] app.ERROR: Erro App\Exception\NotFoundException: Produto não encontrado em /path/to/file.php:42 {
    "exception": {...},
    "request_uri": "/api/produto/123",
    "request_method": "GET",
    "error_code": "NOT_FOUND",
    "details": {"resource": "produto"}
}
```

---

## 📚 Documentação Completa

Veja o guia completo de uso em: [EXEMPLO_USO_EXCEPTIONS.md](./EXEMPLO_USO_EXCEPTIONS.md)

---

## ✅ Checklist de Implementação

- [x] Exceptions customizadas criadas
- [x] ExceptionSubscriber implementado
- [x] RequestIdSubscriber implementado
- [x] ControllerBase atualizado
- [x] Monolog configurado
- [x] Services.yaml atualizado
- [x] Bundles.php atualizado
- [x] Composer.json atualizado
- [x] Exemplo de uso criado
- [x] Documentação criada

---

## 🎯 Próximos Passos

1. **Instalar dependências**: `composer require monolog/monolog symfony/monolog-bundle`
2. **Testar**: Fazer uma requisição que cause erro e verificar a resposta
3. **Migrar controllers existentes**: Substituir `return $this->error()` por `throw new Exception()`
4. **Configurar logs em produção**: Ajustar níveis de log conforme necessário

---

## 🐛 Troubleshooting

### Erro: "Class 'Monolog\Logger' not found"
**Solução:** Execute `composer require monolog/monolog symfony/monolog-bundle`

### Erro: "ExceptionSubscriber not found"
**Solução:** Limpe o cache: `php bin/console cache:clear`

### Logs não aparecem
**Solução:** Verifique permissões da pasta `var/log/` e se o MonologBundle está registrado

---

**Status:** ✅ Implementação Completa
**Data:** 2024

