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
segments_insert = "INSERT INTO `segmentos` VALUES (1,'empresas',CURRENT_TIMESTAMP,NULL);"

diretoria_id = 1
diretorias_insert = "INSERT INTO `diretorias` VALUES (1,1,'empresas',CURRENT_TIMESTAMP,NULL);"

regionais = [
    (1, 'regional norte'),
    (2, 'regional sul'),
    (3, 'regional leste'),
    (4, 'regional oeste'),
    (5, 'regional centro'),
]

agencias = []
agency_id = 1
for reg_id, reg_name in regionais:
    for i in range(1, 6):
        agencias.append((agency_id, reg_id, f'agencia {reg_id}-{i}', 'M'))
        agency_id += 1

entries = []
funcional_counter = 1

def next_func(prefix):
    global funcional_counter
    code = f"{prefix}{funcional_counter:04d}"
    funcional_counter += 1
    return code

entries.append((next_func('D'), 'Diretoria Geral', 1, segment_id, diretoria_id, None, None))
for reg_id, reg_name in regionais:
    entries.append((next_func('R'), f'Gerente Regional {reg_name.title()}', 2, segment_id, diretoria_id, reg_id, None))

gestores = []
gerentes = []
for agency_id_val, reg_id, name, porte in agencias:
    gestor_func = next_func('I')
    gestores.append((gestor_func, f'Gestor {name}', 3, segment_id, diretoria_id, reg_id, agency_id_val))
    for i in range(1, 6):
        ger_func = f"G{agency_id_val:02d}{i:02d}"
        gerentes.append((ger_func, f'Gerente {name} #{i}', 4, segment_id, diretoria_id, reg_id, agency_id_val))

entries.extend(gestores)
entries.extend(gerentes)

# Calendar Aug-Dec 2025
def daterange(start, end):
    cur = start
    while cur <= end:
        yield cur
        cur += datetime.timedelta(days=1)

month_names = {
    1: 'janeiro', 2: 'fevereiro', 3: 'março', 4: 'abril', 5: 'maio', 6: 'junho',
    7: 'julho', 8: 'agosto', 9: 'setembro', 10: 'outubro', 11: 'novembro', 12: 'dezembro'
}
weekday_names = ['segunda','terça','quarta','quinta','sexta','sábado','domingo']

cal_rows = []
for d in daterange(datetime.date(2025, 8, 1), datetime.date(2025, 12, 31)):
    cal_rows.append((d, d.year, d.month, month_names[d.month], d.day, weekday_names[d.weekday()],
                     int(d.strftime('%U')) % 54, (d.month - 1)//3 + 1, (d.month - 1)//6 + 1,
                     0 if d.weekday() >=5 else 1))

months = [datetime.date(2025, m, 1) for m in range(8, 13)]
products = [6,7,8,9,10]

meta_rows = []
real_rows = []
pontos_rows = []
variavel_rows = []
detalhes_rows = []
contract_counter = 1
registro_counter = 1
for g_idx, (funcional, nome, cargo, seg, dir_id, reg_id, ag_id) in enumerate(gerentes, start=1):
    for month in months:
        for prod in products:
            base = 1000 + prod*20 + month.month*10 + (g_idx % 10) * 5
            meta_rows.append((month, funcional, prod, round(base,2)))
            realizado_val = round(base * 0.92, 2)
            real_rows.append((f"R{contract_counter:04d}", funcional, month, realizado_val, prod))
            contract_counter += 1
            pontos_rows.append((funcional, prod, base, realizado_val, month))
            variavel_rows.append((funcional, month, round(base*0.5,2), round(realizado_val*0.45,2)))
            detalhes_rows.append((f"CT{registro_counter:05d}", f"RG{registro_counter:05d}", funcional, prod, month, month, base, realizado_val, 1, 0.5, realizado_val*0.1, None, None, None, 'canal direto', 'tipo base', 'avista', '1'))
            registro_counter += 1

cal_insert = "INSERT INTO `d_calendario` VALUES " + ",".join(
    "(%s,%d,%d,'%s',%d,'%s',%d,%d,%d,%d)" % (
        f"'{d.strftime('%Y-%m-%d')}'", ano, mes, mes_nome, dia, dia_sem, semana, tri, sem,
        util
    ) for d, ano, mes, mes_nome, dia, dia_sem, semana, tri, sem, util in cal_rows
) + ";"

ag_insert = "INSERT INTO `agencias` VALUES " + ",".join(
    f"({aid},{rid},'{nome}','{porte}',CURRENT_TIMESTAMP,NULL)" for aid, rid, nome, porte in agencias
) + ";"

region_insert = "INSERT INTO `regionais` VALUES " + ",".join(
    f"({rid},{diretoria_id},'{nome}',CURRENT_TIMESTAMP,NULL)" for rid, nome in regionais
) + ";"

cargo_block = extract_full_block('cargos')
familia_block = extract_full_block('familia')
indicador_block = extract_full_block('indicador')
subindicador_block = extract_full_block('subindicador')
d_produtos_block = extract_full_block('d_produtos')
d_status_block = extract_full_block('d_status_indicadores')
grupo_block = extract_full_block('grupos')
migration_block = extract_full_block('migration_versions')
omega_blocks = [extract_full_block(t) for t in ['omega_categorias','omega_chamados','omega_departamentos','omega_status','omega_usuarios']]
f_campanhas_block = extract_full_block('f_campanhas')
f_leads_block = extract_full_block('f_leads_propensos')

estrutura_insert = "INSERT INTO `d_estrutura` VALUES " + ",".join(
    f"({idx+1},'{func}', '{nome}', {cargo_id},{segment_id},{diretoria_id},{reg_id if reg_id is not None else 'NULL'},{ag_id if ag_id is not None else 'NULL'},CURRENT_TIMESTAMP,NULL,NULL)" for idx,(func, nome, cargo_id, seg,did,reg_id,ag_id) in enumerate(entries)
) + ";"

meta_insert = "INSERT INTO `f_meta` (data_meta, funcional, produto_id, meta_mensal, created_at, updated_at) VALUES " + ",".join(
    f"('{dt.strftime('%Y-%m-%d')}','{func}',{prod},{meta:.2f},CURRENT_TIMESTAMP,NULL)" for dt, func, prod, meta in meta_rows
) + ";"

real_insert = "INSERT INTO `f_realizados` (id_contrato, funcional, data_realizado, realizado, produto_id) VALUES " + ",".join(
    f"('{cid}','{func}','{dt.strftime('%Y-%m-%d')}',{real:.2f},{prod})" for cid, func, dt, real, prod in real_rows
) + ";"

pontos_insert = "INSERT INTO `f_pontos` (funcional, produto_id, meta, realizado, data_realizado) VALUES " + ",".join(
    f"('{func}',{prod},{meta:.2f},{real:.2f},'{dt.strftime('%Y-%m-%d')}')" for func, prod, meta, real, dt in pontos_rows
) + ";"

variavel_insert = "INSERT INTO `f_variavel` (funcional, meta, variavel, dt_atualizacao) VALUES " + ",".join(
    f"('{func}',{meta:.2f},{var:.2f},'{dt.strftime('%Y-%m-%d')}')" for func, dt, meta, var in variavel_rows
) + ";"

detalhes_insert = "INSERT INTO `f_detalhes` (contrato_id, registro_id, funcional, id_produto, dt_cadastro, competencia, valor_meta, valor_realizado, quantidade, peso, pontos, dt_vencimento, dt_cancelamento, motivo_cancelamento, canal_venda, tipo_venda, condicao_pagamento, status_id) VALUES " + ",".join(
    "('%s','%s','%s',%d,'%s','%s',%.2f,%.2f,%.4f,%.4f,%.4f,%s,%s,%s,'%s','%s','%s','%s')" % (
        contrato, registro, func, prod, dt.strftime('%Y-%m-%d'), comp.strftime('%Y-%m-%d'), vm, vr, qtd, peso, pts,
        'NULL','NULL','NULL', canal, tipo, condicao, status
    ) for contrato, registro, func, prod, dt, comp, vm, vr, qtd, peso, pts, venc, cancel, motivo, canal, tipo, condicao, status in detalhes_rows
) + ";"

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
    context = new_content[idx-50:idx+50]
    raise SystemExit(f"Placeholder found {occurrences} times near: {context}")
source.write_text(new_content)
print("Dump regenerated")
