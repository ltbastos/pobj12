#!/usr/bin/env python3
"""
Script para remover comentários de arquivos TypeScript, JavaScript, Vue e CSS
"""
import re
import os
from pathlib import Path

def remove_ts_js_comments(content: str) -> str:
    """Remove comentários de TypeScript/JavaScript"""
    lines = content.split('\n')
    result = []
    in_block_comment = False
    in_string = False
    string_char = None
    
    i = 0
    while i < len(lines):
        line = lines[i]
        new_line = ''
        j = 0
        
        while j < len(line):
            char = line[j]
            
            # Detecta strings
            if not in_string and (char == '"' or char == "'" or char == '`'):
                in_string = True
                string_char = char
                new_line += char
                j += 1
                continue
            elif in_string and char == string_char:
                # Verifica se não é escape
                if j == 0 or line[j-1] != '\\':
                    in_string = False
                    string_char = None
                new_line += char
                j += 1
                continue
            
            if not in_string:
                # Comentário de linha
                if j < len(line) - 1 and line[j:j+2] == '//':
                    # Preserva eslint-disable
                    if 'eslint-disable' in line[j:] or 'eslint-enable' in line[j:]:
                        new_line += line[j:]
                    break
                
                # Comentário de bloco
                if j < len(line) - 1 and line[j:j+2] == '/*':
                    # Procura o fim do comentário
                    end = line.find('*/', j + 2)
                    if end != -1:
                        # Comentário na mesma linha
                        j = end + 2
                        continue
                    else:
                        # Comentário multilinha
                        in_block_comment = True
                        j += 2
                        continue
                
                if in_block_comment:
                    end = line.find('*/', j)
                    if end != -1:
                        in_block_comment = False
                        j = end + 2
                        continue
                    else:
                        j = len(line)
                        continue
            
            new_line += char
            j += 1
        
        if new_line.strip() or not result or result[-1].strip():
            result.append(new_line)
        i += 1
    
    return '\n'.join(result)

def remove_css_comments(content: str) -> str:
    """Remove comentários CSS"""
    # Remove comentários de bloco /* */
    pattern = r'/\*[^*]*\*+(?:[^/*][^*]*\*+)*/'
    content = re.sub(pattern, '', content)
    return content

def remove_vue_comments(content: str) -> str:
    """Remove comentários de arquivos Vue"""
    # Remove comentários HTML <!-- -->
    content = re.sub(r'<!--.*?-->', '', content, flags=re.DOTALL)
    
    # Processa seções <script>
    script_pattern = r'(<script[^>]*>)(.*?)(</script>)'
    def process_script(match):
        tag_open = match.group(1)
        script_content = match.group(2)
        tag_close = match.group(3)
        cleaned_script = remove_ts_js_comments(script_content)
        return tag_open + cleaned_script + tag_close
    
    content = re.sub(script_pattern, process_script, content, flags=re.DOTALL | re.IGNORECASE)
    
    # Processa seções <style>
    style_pattern = r'(<style[^>]*>)(.*?)(</style>)'
    def process_style(match):
        tag_open = match.group(1)
        style_content = match.group(2)
        tag_close = match.group(3)
        cleaned_style = remove_css_comments(style_content)
        return tag_open + cleaned_style + tag_close
    
    content = re.sub(style_pattern, process_style, content, flags=re.DOTALL | re.IGNORECASE)
    
    return content

def process_file(file_path: Path):
    """Processa um arquivo removendo comentários"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        if file_path.suffix == '.vue':
            content = remove_vue_comments(content)
        elif file_path.suffix in ['.ts', '.js', '.tsx', '.jsx']:
            content = remove_ts_js_comments(content)
        elif file_path.suffix == '.css':
            content = remove_css_comments(content)
        else:
            return
        
        # Remove linhas vazias excessivas (máximo 2 consecutivas)
        lines = content.split('\n')
        cleaned_lines = []
        empty_count = 0
        for line in lines:
            if not line.strip():
                empty_count += 1
                if empty_count <= 2:
                    cleaned_lines.append(line)
            else:
                empty_count = 0
                cleaned_lines.append(line)
        content = '\n'.join(cleaned_lines)
        
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Processado: {file_path}")
    except Exception as e:
        print(f"Erro ao processar {file_path}: {e}")

def main():
    """Função principal"""
    src_dir = Path('src')
    
    if not src_dir.exists():
        print("Diretório src não encontrado!")
        return
    
    # Processa todos os arquivos relevantes
    extensions = ['.ts', '.js', '.tsx', '.jsx', '.vue', '.css']
    
    for ext in extensions:
        for file_path in src_dir.rglob(f'*{ext}'):
            if 'node_modules' in str(file_path) or 'dist' in str(file_path):
                continue
            process_file(file_path)
    
    # Processa arquivos na raiz do frontend
    root_files = ['vite.config.ts', 'eslint.config.ts']
    for file_name in root_files:
        file_path = Path(file_name)
        if file_path.exists():
            process_file(file_path)
    
    print("Concluído!")

if __name__ == '__main__':
    main()

