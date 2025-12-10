import datetime
from pathlib import Path
import re

root = Path('.').resolve()
source = root / 'dump-pobj-202512092207.sql'
content = source.read_text()

# Helper to extract create section and full block from original dump
def extract_section(table: str):
    pattern = rf"-- Table structure for table `{table}`\n(.*?)--\n-- Dumping data for table `{table}`\n--\n\n"
    m = re.search(pattern, content, re.S)
    if not m:
        raise SystemExit(f"create block not found for {table}")
    return f"-- Table structure for table `{table}`\n{m.group(1)}--\n-- Dumping data for table `{table}`\n--\n\n"


def extract_full_block(table: str):
    pattern = rf"-- Table structure for table `{table}`.*?UNLOCK TABLES;\n"
    m = re.search(pattern, content, re.S)
    if not m:
        raise SystemExit(f"full block not found for {table}")
    return m.group(0)


header_end = content.find("-- Table structure for table `segmentos`")
header = content[:header_end]

segment_id = 1
segments_insert = "INSERT INTO `segmentos` VALUES (1,'empresas','2025-12-08 02:35:54',NULL);"

diretoria_id = 8607
diretorias_insert = "INSERT INTO `diretorias` VALUES (8607,1,'empresas','2025-12-08 02:35:56',NULL);"

regionais = [
    (8486, 'sul e oeste', '2025-12-08 02:35:56'),
    (8487, 'sp capital leste', '2025-12-08 02:35:56'),
    (8488, 'sp capital norte', '2025-12-08 05:35:56'),
    (8489, 'sp interior norte', '2025-12-08 05:35:56'),
    (8490, 'sp interior sul', '2025-12-08 05:35:56'),
]

agencias = [
    (1009, 8486, 'porto alegre 1', 'M', '2025-12-08 02:35:57'),
    (1141, 8487, 'campo limpo', 'M', '2025-12-08 02:35:59'),
    (1233, 8486, 'parana 1', 'M', '2025-12-08 02:35:57'),
    (1234, 8487, 'faria lima 1', 'M', '2025-12-08 02:35:59'),
    (1267, 8487, 'faria lima 2', 'M', '2025-12-08 02:35:59'),
    (3345, 8486, 'Curitiba 2', 'M', '2025-12-08 02:35:57'),
    (3346, 8486, 'porto alegre 2', 'M', '2025-12-08 05:35:57'),
    (3347, 8486, 'parana 2', 'M', '2025-12-08 05:35:57'),
    (3348, 8487, 'faria lima 3', 'M', '2025-12-08 05:35:59'),
    (3349, 8487, 'butanta 1', 'M', '2025-12-08 05:35:59'),
    (3350, 8488, 'santana', 'M', '2025-12-08 05:36:00'),
    (3351, 8488, 'tucuruvi', 'M', '2025-12-08 05:36:00'),
    (3352, 8488, 'vila maria', 'M', '2025-12-08 05:36:00'),
    (3353, 8488, 'tatuape', 'M', '2025-12-08 05:36:00'),
    (3354, 8488, 'mooca', 'M', '2025-12-08 05:36:00'),
    (3355, 8489, 'campinas centro', 'M', '2025-12-08 05:36:00'),
    (3356, 8489, 'ribeirao preto 1', 'M', '2025-12-08 05:36:00'),
    (3357, 8489, 'ribeirao preto 2', 'M', '2025-12-08 05:36:00'),
    (3358, 8489, 'sao jose rio preto', 'M', '2025-12-08 05:36:00'),
    (3359, 8489, 'piracicaba', 'M', '2025-12-08 05:36:00'),
    (3360, 8490, 'sorocaba centro', 'M', '2025-12-08 05:36:00'),
    (3361, 8490, 'jundiai 1', 'M', '2025-12-08 05:36:00'),
    (3362, 8490, 'jundiai 2', 'M', '2025-12-08 05:36:00'),
    (3363, 8490, 'santos 1', 'M', '2025-12-08 05:36:00'),
    (3364, 8490, 'santos 2', 'M', '2025-12-08 05:36:00'),
]

estrutura_base = [
    (116, 'I020219', 'andre gestor', 3, 1, 8607, 8486, 1009, '2025-12-08 02:45:08'),
    (117, 'G123456', 'Pedro gerente', 4, 1, 8607, 8486, 1009, '2025-12-08 02:45:08'),
    (118, 'G987654', 'Maria gerente', 4, 1, 8607, 8486, 1009, '2025-12-08 02:45:08'),
    (119, 'G887766', 'Carla gerente', 4, 1, 8607, 8486, 1009, '2025-12-08 02:45:08'),
    (120, 'I020220', 'Marcio gestor', 3, 1, 8607, 8486, 1233, '2025-12-08 02:45:11'),
    (121, 'G99999', 'henrique gerente', 4, 1, 8607, 8486, 1233, '2025-12-08 02:45:11'),
    (122, 'G88888', 'Bruno gerente', 4, 1, 8607, 8486, 1233, '2025-12-08 02:45:11'),
    (123, 'G77777', 'cristian gerente', 4, 1, 8607, 8486, 1233, '2025-12-08 02:45:11'),
    (124, 'I020230', 'Giseli gestora', 3, 1, 8607, 8486, 3345, '2025-12-08 02:45:14'),
    (125, 'G66666', 'Andreia gerente', 4, 1, 8607, 8486, 3345, '2025-12-08 02:45:14'),
    (126, 'G55555', 'Jaqueline gerente', 4, 1, 8607, 8486, 3345, '2025-12-08 02:45:14'),
    (127, 'I020240', 'João gestor', 3, 1, 8607, 8487, 1141, '2025-12-08 02:45:17'),
    (128, 'G44444', 'Marcela gerente', 4, 1, 8607, 8487, 1141, '2025-12-08 02:45:17'),
    (129, 'G33333', 'Juliana gerente', 4, 1, 8607, 8487, 1141, '2025-12-08 02:45:17'),
    (130, 'I020250', 'Alessandra gestora', 3, 1, 8607, 8487, 1267, '2025-12-08 02:45:20'),
    (131, 'G22222', 'Ana gerente', 4, 1, 8607, 8487, 1267, '2025-12-08 02:45:20'),
    (132, 'G11111', 'Lucas gerente', 4, 1, 8607, 8487, 1267, '2025-12-08 02:45:20'),
    (133, 'I020260', 'alisson gestor', 3, 1, 8607, 8487, 1234, '2025-12-08 02:45:21'),
    (134, 'G00000', 'cleber gerente', 4, 1, 8607, 8487, 1234, '2025-12-08 02:45:21'),
    (135, 'G00001', 'Paulo gerente', 4, 1, 8607, 8486, 1009, '2025-12-08 05:50:00'),
    (136, 'G00002', 'Renata gerente', 4, 1, 8607, 8486, 1009, '2025-12-08 05:50:00'),
    (137, 'G00003', 'Tiago gerente', 4, 1, 8607, 8486, 1233, '2025-12-08 05:50:00'),
    (138, 'G00004', 'Fernanda gerente', 4, 1, 8607, 8486, 1233, '2025-12-08 05:50:00'),
    (139, 'G00005', 'Rafael gerente', 4, 1, 8607, 8486, 3345, '2025-12-08 05:50:00'),
    (140, 'G00006', 'Patricia gerente', 4, 1, 8607, 8486, 3345, '2025-12-08 05:50:00'),
    (141, 'G00007', 'Diego gerente', 4, 1, 8607, 8486, 3345, '2025-12-08 05:50:00'),
    (142, 'G00008', 'Camila gerente', 4, 1, 8607, 8487, 1141, '2025-12-08 05:50:00'),
    (143, 'G00009', 'Bruno gerente', 4, 1, 8607, 8487, 1141, '2025-12-08 05:50:00'),
    (144, 'G00010', 'Luciana gerente', 4, 1, 8607, 8487, 1141, '2025-12-08 05:50:00'),
    (145, 'G00011', 'Eduardo gerente', 4, 1, 8607, 8487, 1267, '2025-12-08 05:50:00'),
    (146, 'G00012', 'Bianca gerente', 4, 1, 8607, 8487, 1267, '2025-12-08 05:50:00'),
    (147, 'G00013', 'Rodrigo gerente', 4, 1, 8607, 8487, 1267, '2025-12-08 05:50:00'),
    (148, 'G00014', 'Caroline gerente', 4, 1, 8607, 8487, 1234, '2025-12-08 05:50:00'),
    (149, 'G00015', 'Felipe gerente', 4, 1, 8607, 8487, 1234, '2025-12-08 05:50:00'),
    (150, 'G00016', 'Simone gerente', 4, 1, 8607, 8487, 1234, '2025-12-08 05:50:00'),
    (151, 'G00017', 'Marcos gerente', 4, 1, 8607, 8487, 1234, '2025-12-08 05:50:00'),
]

entries = list(estrutura_base)
next_id = max(e[0] for e in entries) + 1
max_g_num = max(int(e[1][1:]) for e in entries if e[1].startswith('G') and e[1][1:].isdigit())
max_i_num = max(int(e[1][1:]) for e in entries if e[1].startswith('I') and e[1][1:].isdigit())

created_new = '2025-12-09 00:00:00'

for agency_id, regional_id, agency_name, _, _ in agencias:
    gestores = [e for e in entries if e[7] == agency_id and e[3] == 3]
    if not gestores:
        func = f"I{max_i_num + 1:06d}"
        max_i_num += 1
        entries.append(
            (
                next_id,
                func,
                f'Gestor {agency_name}',
                3,
                segment_id,
                diretoria_id,
                regional_id,
                agency_id,
                created_new,
            )
        )
        next_id += 1
    gerentes = [e for e in entries if e[7] == agency_id and e[3] == 4]
    while len(gerentes) < 5:
        func = f"G{max_g_num + 1:05d}"
        max_g_num += 1
        new_entry = (
            next_id,
            func,
            f'Gerente {agency_name} {len(gerentes) + 1}',
            4,
            segment_id,
            diretoria_id,
            regional_id,
            agency_id,
            created_new,
        )
        entries.append(new_entry)
        gerentes.append(new_entry)
        next_id += 1

# Calendar Jan-Dec 2025
def daterange(start, end):
    cur = start
    while cur <= end:
        yield cur
        cur += datetime.timedelta(days=1)


month_names = {
    1: 'janeiro',
    2: 'fevereiro',
    3: 'março',
    4: 'abril',
    5: 'maio',
    6: 'junho',
    7: 'julho',
    8: 'agosto',
    9: 'setembro',
    10: 'outubro',
    11: 'novembro',
    12: 'dezembro',
}
weekday_names = ['segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado', 'domingo']

start_date = datetime.date(2025, 1, 1)
end_date = datetime.date(2025, 12, 9)

cal_rows = []
for d in daterange(start_date, end_date):
    cal_rows.append(
        (
            d,
            d.year,
            d.month,
            month_names[d.month],
            d.day,
            weekday_names[d.weekday()],
            int(d.strftime('%U')) % 54,
            (d.month - 1) // 3 + 1,
            (d.month - 1) // 6 + 1,
            0 if d.weekday() >= 5 else 1,
        )
    )

def month_starts(start: datetime.date, end: datetime.date):
    cur = datetime.date(start.year, start.month, 1)
    while cur <= end:
        yield cur
        if cur.month == 12:
            cur = datetime.date(cur.year + 1, 1, 1)
        else:
            cur = datetime.date(cur.year, cur.month + 1, 1)


months = list(month_starts(start_date, end_date))
products = [6, 7, 8, 9, 10]

meta_rows = []
real_rows = []
pontos_rows = []
variavel_rows = []
detalhes_rows = []
contract_counter = 1
registro_counter = 1

all_gerentes = [e for e in entries if e[3] == 4]
for g_idx, (_, funcional, _, _, _, _, reg_id, ag_id, _) in enumerate(all_gerentes, start=1):
    for month in months:
        for prod in products:
            base = 1000 + prod * 20 + month.month * 10 + (g_idx % 10) * 5
            meta_rows.append((month, funcional, prod, round(base, 2)))
            realizado_val = round(base * 0.92, 2)
            real_rows.append((f"R{contract_counter:04d}", funcional, month, realizado_val, prod))
            contract_counter += 1
            pontos_rows.append((funcional, prod, base, realizado_val, month))
            variavel_rows.append((funcional, month, round(base * 0.5, 2), round(realizado_val * 0.45, 2)))
            detalhes_rows.append(
                (
                    f"CT{registro_counter:05d}",
                    f"RG{registro_counter:05d}",
                    funcional,
                    prod,
                    month,
                    month,
                    base,
                    realizado_val,
                    1,
                    0.5,
                    realizado_val * 0.1,
                    None,
                    None,
                    None,
                    'canal direto',
                    'tipo base',
                    'avista',
                    '1',
                )
            )
            registro_counter += 1

cal_insert = "INSERT INTO `d_calendario` VALUES " + ",".join(
    "(%s,%d,%d,'%s',%d,'%s',%d,%d,%d,%d)"
    % (
        f"'{d.strftime('%Y-%m-%d')}'",
        ano,
        mes,
        mes_nome,
        dia,
        dia_sem,
        semana,
        tri,
        sem,
        util,
    )
    for d, ano, mes, mes_nome, dia, dia_sem, semana, tri, sem, util in cal_rows
) + ";"

ag_insert = "INSERT INTO `agencias` VALUES " + ",".join(
    f"({aid},{rid},'{nome}','{porte}','{created}',NULL)" for aid, rid, nome, porte, created in agencias
) + ";"

region_insert = "INSERT INTO `regionais` VALUES " + ",".join(
    f"({rid},{diretoria_id},'{nome}','{created}',NULL)" for rid, nome, created in regionais
) + ";"

cargo_block = extract_full_block('cargos')
familia_block = extract_full_block('familia')
indicador_block = extract_full_block('indicador')
subindicador_block = extract_full_block('subindicador')
d_produtos_block = extract_full_block('d_produtos')
d_status_block = extract_full_block('d_status_indicadores')
grupo_block = extract_full_block('grupos')
migration_block = extract_full_block('migration_versions')
omega_blocks = [
    extract_full_block(t)
    for t in [
        'omega_categorias',
        'omega_chamados',
        'omega_departamentos',
        'omega_status',
        'omega_usuarios',
    ]
]
f_campanhas_block = extract_full_block('f_campanhas')
f_leads_block = extract_full_block('f_leads_propensos')

estrutura_insert = "INSERT INTO `d_estrutura` VALUES " + ",".join(
    "(%d,'%s','%s',%d,%d,%d,%d,%d,'%s',NULL,NULL)"
    % (idx, func, nome, cargo_id, segment_id, diretoria_id, reg_id, ag_id, created)
    for idx, func, nome, cargo_id, seg, did, reg_id, ag_id, created in entries
) + ";"

meta_insert = (
    "INSERT INTO `f_meta` (data_meta, funcional, produto_id, meta_mensal, created_at, updated_at) VALUES "
    + ",".join(
        f"('{dt.strftime('%Y-%m-%d')}','{func}',{prod},{meta:.2f},CURRENT_TIMESTAMP,NULL)"
        for dt, func, prod, meta in meta_rows
    )
    + ";"
)

real_insert = "INSERT INTO `f_realizados` (id_contrato, funcional, data_realizado, realizado, produto_id) VALUES " + ",".join(
    f"('{cid}','{func}','{dt.strftime('%Y-%m-%d')}',{real:.2f},{prod})" for cid, func, dt, real, prod in real_rows
) + ";"

pontos_insert = "INSERT INTO `f_pontos` (funcional, produto_id, meta, realizado, data_realizado) VALUES " + ",".join(
    f"('{func}',{prod},{meta:.2f},{real:.2f},'{dt.strftime('%Y-%m-%d')}')" for func, prod, meta, real, dt in pontos_rows
) + ";"

variavel_insert = "INSERT INTO `f_variavel` (funcional, meta, variavel, dt_atualizacao) VALUES " + ",".join(
    f"('{func}',{meta:.2f},{var:.2f},'{dt.strftime('%Y-%m-%d')}')" for func, dt, meta, var in variavel_rows
) + ";"

detalhes_insert = (
    "INSERT INTO `f_detalhes` (contrato_id, registro_id, funcional, id_produto, dt_cadastro, competencia, valor_meta, valor_realizado, quantidade, peso, pontos, dt_vencimento, dt_cancelamento, motivo_cancelamento, canal_venda, tipo_venda, condicao_pagamento, status_id) VALUES "
    + ",".join(
        "('%s','%s','%s',%d,'%s','%s',%.2f,%.2f,%.4f,%.4f,%.4f,%s,%s,%s,'%s','%s','%s','%s')"
        % (
            contrato,
            registro,
            func,
            prod,
            dt.strftime('%Y-%m-%d'),
            comp.strftime('%Y-%m-%d'),
            vm,
            vr,
            qtd,
            peso,
            pts,
            'NULL',
            'NULL',
            'NULL',
            canal,
            tipo,
            condicao,
            status,
        )
        for contrato, registro, func, prod, dt, comp, vm, vr, qtd, peso, pts, venc, cancel, motivo, canal, tipo, condicao, status in detalhes_rows
    )
    + ";"
)

output = [header]


def section_with_insert(table, insert_sql):
    create_part = extract_section(table)
    return (
        create_part
        + f"LOCK TABLES `{table}` WRITE;\n/*!40000 ALTER TABLE `{table}` DISABLE KEYS */;\n"
        + insert_sql
        + f"\n/*!40000 ALTER TABLE `{table}` ENABLE KEYS */;\nUNLOCK TABLES;\n\n"
    )


output.append(section_with_insert('segmentos', segments_insert))
output.append(section_with_insert('diretorias', diretorias_insert))
output.append(section_with_insert('regionais', region_insert))
output.append(section_with_insert('agencias', ag_insert))
output.append(cargo_block)
output.append(section_with_insert('d_calendario', cal_insert))
output.append(grupo_block)
output.append(familia_block)
output.append(indicador_block)
output.append(subindicador_block)
output.append(d_produtos_block)
output.append(d_status_block)
output.append(section_with_insert('d_estrutura', estrutura_insert))
output.append(section_with_insert('f_meta', meta_insert))
output.append(section_with_insert('f_realizados', real_insert))
output.append(section_with_insert('f_pontos', pontos_insert))
output.append(section_with_insert('f_variavel', variavel_insert))
output.append(section_with_insert('f_detalhes', detalhes_insert))
output.append(f_campanhas_block)
output.append(f_leads_block)
output.extend(omega_blocks)
output.append(migration_block)

new_content = "".join(output)
occurrences = new_content.count("{table}")
if occurrences:
    idx = new_content.index("{table}")
    context = new_content[idx - 50 : idx + 50]
    raise SystemExit(f"Placeholder found {occurrences} times near: {context}")
source.write_text(new_content)
print("Dump regenerated")
