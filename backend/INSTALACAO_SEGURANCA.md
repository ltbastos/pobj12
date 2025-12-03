# 🚀 Guia de Instalação - Segurança

## 📋 Passo a Passo

### 1. Instalar Dependências

```bash
cd Backend
composer require lexik/jwt-authentication-bundle symfony/security-bundle symfony/security-csrf
composer update
```

**Nota sobre Rate Limiter:**
O `symfony/rate-limiter` pode não estar disponível no Symfony 4.4. O `RateLimitSubscriber` implementa uma solução customizada que não requer essa dependência.

### 2. Configurar API Key

Adicione no arquivo `.env`:
```
API_KEY=sua-chave-secreta-aqui
```

**Importante:** Use uma chave forte! Gere uma com:
```bash
openssl rand -hex 32
```

### 3. Limpar Cache

```bash
php bin/console cache:clear
```

### 4. Testar Autenticação

```bash
# Sem API Key (deve retornar 401)
curl http://localhost/api/pobj/resumo

# Com API Key (deve funcionar)
curl -H "X-API-Key: sua-api-key-aqui" http://localhost/api/pobj/resumo
```

---

## ⚙️ Configurações

### Rate Limiting

Ajuste os limites em `config/services.yaml`:

```yaml
App\EventSubscriber\RateLimitSubscriber:
    arguments:
        $rateLimits:
            default: { limit: 100, window: 60 }    # 100 req/min
            auth: { limit: 5, window: 60 }         # 5 req/min
            api: { limit: 1000, window: 3600 }      # 1000 req/hora
```

### CSRF Protection

Para desabilitar CSRF em rotas específicas, edite `config/services.yaml`:

```yaml
App\EventSubscriber\CsrfProtectionSubscriber:
    arguments:
        $enabled: true
        $excludedPaths:
            - '/api/auth/login'
            - '/api/auth/register'
            - '/api/publico'  # Adicione aqui
```

### Security Firewall

Edite `config/packages/security.yaml` para ajustar:
- Rotas públicas
- Roles necessárias
- Firewalls

---

## 🔧 Configuração de JWT (Opcional)

Se quiser usar JWT além de API Keys:

### 1. Gerar Chaves

```bash
php bin/console lexik:jwt:generate-keypair
```

### 2. Configurar Variáveis de Ambiente

Adicione ao `.env`:
```
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=sua-senha-aqui
```

### 3. Configurar Bundle

Crie `config/packages/lexik_jwt_authentication.yaml`:
```yaml
lexik_jwt_authentication:
    secret_key: '%env(JWT_SECRET_KEY)%'
    public_key: '%env(JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600
```

---

## ✅ Verificação

### Testar Rate Limiting

Faça muitas requisições rapidamente:
```bash
for i in {1..110}; do
  curl -H "X-API-Key: sua-key" http://localhost/api/pobj/resumo
done
```

A 101ª requisição deve retornar 429.

### Testar CSRF

```bash
# Sem token (deve falhar)
curl -X POST http://localhost/api/endpoint

# Com token (deve funcionar)
curl -X POST \
  -H "X-CSRF-Token: token-aqui" \
  http://localhost/api/endpoint
```

### Testar Sanitização

Os inputs são automaticamente sanitizados quando você usa `InputSanitizer::sanitizeRequestData()`.

---

## 🐛 Troubleshooting

### Erro: "Class not found" (Security classes)
**Solução:** Execute `composer require symfony/security-bundle`

### Erro: "API Key não encontrada" ou "API Key inválida"
**Solução:** 
1. Verifique se a variável `API_KEY` está configurada no arquivo `.env`
2. Verifique se está enviando o header `X-API-Key` corretamente
3. Certifique-se de que a chave no `.env` corresponde à chave enviada

### Erro: "Rate limit excedido" sempre
**Solução:** Limpe o storage de rate limit ou ajuste os limites

### Erro: "Token CSRF inválido"
**Solução:** 
1. Certifique-se de obter um token CSRF válido
2. Envie no header `X-CSRF-Token` ou no body como `_token`

---

## 📚 Próximos Passos

1. **Criar endpoint para gerenciar API Keys** - Use `ApiKeyController` como base
2. **Implementar JWT** - Siga o guia acima
3. **Adicionar logging de segurança** - Logar tentativas falhadas
4. **Implementar rotação de API Keys** - Sistema automático
5. **Adicionar permissões granulares** - Baseado em recursos

---

**Status:** ✅ Pronto para instalação
**Última atualização:** 2024-12-03

