# Guia rápido do frontend

Este frontend foi criado com Vite. Use os comandos abaixo para desenvolver e testar localmente.

## Fluxo recomendado para visualizar mudanças
1. Instale as dependências (somente na primeira vez ou quando `package.json` mudar):
   ```bash
   npm install
   ```
2. Rode em modo de desenvolvimento, com hot reload:
   ```bash
   npm run dev
   ```
   O build de produção **não** é necessário para apenas visualizar as alterações no navegador.

## Quando usar `npm run build`
- Gera a versão otimizada para produção.
- Útil para validar se o projeto compila sem erros antes de subir para o servidor ou abrir PR.
- Mais lento que o modo dev; para revisão local, prefira `npm run dev`.

## Dica: simulador na visão clássica de Resumo
- O botão "Ativar simulação rápida" e o atalho "Limpar ajustes" ficam na **Visão clássica** do Resumo.
- Ative o checkbox e edite os campos de "Meta" e "Realizado" diretamente na tabela para simular cenários. As mudanças são temporárias e somem ao recarregar a página.
