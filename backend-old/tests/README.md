# Testes Unitários

Este diretório contém os testes unitários do projeto.

## Estrutura

```
tests/
├── Unit/                    # Testes unitários
│   ├── Domain/
│   │   └── Enum/           # Testes para Enums
│   └── Infrastructure/
│       └── Helpers/        # Testes para Helpers
└── Integration/             # Testes de integração (a criar)
```

## Executando os Testes

### Executar todos os testes
```bash
vendor/bin/phpunit
```

### Executar apenas testes unitários
```bash
vendor/bin/phpunit tests/Unit
```

### Executar um arquivo de teste específico
```bash
vendor/bin/phpunit tests/Unit/Infrastructure/Helpers/ValueFormatterTest.php
```

### Executar com cobertura de código
```bash
vendor/bin/phpunit --coverage-html coverage/
```

## Requisitos

- PHP 7.1.17 ou superior
- PHPUnit 7.5 (já incluído nas dependências de desenvolvimento)

## Testes Implementados

### Helpers
- ✅ `ValueFormatterTest` - Testa conversão de valores (toFloat, toInt, toString)
- ✅ `DateFormatterTest` - Testa formatação de datas
- ✅ `EnvHelperTest` - Testa carregamento de variáveis de ambiente

### Enums
- ✅ `CargoTest` - Testa enum de Cargos
- ✅ `FiltroNivelTest` - Testa enum de Níveis de Filtro
- ✅ `StatusIndicadorTest` - Testa enum de Status de Indicadores

## Próximos Passos

- [ ] Testes para UseCases
- [ ] Testes para Repositories (com mocks)
- [ ] Testes para Controllers
- [ ] Testes para DTOs
- [ ] Testes para Entities


