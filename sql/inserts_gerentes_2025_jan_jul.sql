-- Popula metas de janeiro a julho de 2025 tomando agosto/2025 como base.
-- Gera valores de meta reduzindo 1000 por mês em relação a agosto para manter a progressão mensal.
-- Evita duplicação caso alguma dessas competências já esteja inserida.

WITH RECURSIVE meses AS (
  SELECT 1 AS mes
  UNION ALL
  SELECT mes + 1 FROM meses WHERE mes < 7
),
base AS (
  SELECT funcional, produto_id, meta_mensal
  FROM f_meta
  WHERE data_meta = '2025-08-01'
)
INSERT INTO f_meta (data_meta, funcional, produto_id, meta_mensal)
SELECT DATE_ADD('2025-01-01', INTERVAL meses.mes - 1 MONTH) AS data_meta,
       base.funcional,
       base.produto_id,
       base.meta_mensal - (1000 * (8 - meses.mes)) AS meta_mensal
FROM base
JOIN meses
LEFT JOIN f_meta existentes
  ON existentes.funcional = base.funcional
 AND existentes.produto_id = base.produto_id
 AND existentes.data_meta = DATE_ADD('2025-01-01', INTERVAL meses.mes - 1 MONTH)
WHERE existentes.funcional IS NULL;
