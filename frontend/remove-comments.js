const fs = require('fs');
const path = require('path');

function removeTSJSComments(content) {
  const lines = content.split('\n');
  const result = [];
  let inBlockComment = false;
  let inString = false;
  let stringChar = null;
  
  for (let i = 0; i < lines.length; i++) {
    let line = lines[i];
    let newLine = '';
    let j = 0;
    
    while (j < line.length) {
      const char = line[j];
      
      if (!inString && (char === '"' || char === "'" || char === '`')) {
        inString = true;
        stringChar = char;
        newLine += char;
        j++;
        continue;
      } else if (inString && char === stringChar) {
        if (j === 0 || line[j-1] !== '\\') {
          inString = false;
          stringChar = null;
        }
        newLine += char;
        j++;
        continue;
      }
      
      if (!inString) {
        if (j < line.length - 1 && line.substring(j, j+2) === '//') {
          const rest = line.substring(j);
          if (rest.includes('eslint-disable') || rest.includes('eslint-enable')) {
            newLine += line.substring(j);
          }
          break;
        }
        
        if (j < line.length - 1 && line.substring(j, j+2) === '/*') {
          const end = line.indexOf('*/', j + 2);
          if (end !== -1) {
            j = end + 2;
            continue;
          } else {
            inBlockComment = true;
            j += 2;
            continue;
          }
        }
        
        if (inBlockComment) {
          const end = line.indexOf('*/', j);
          if (end !== -1) {
            inBlockComment = false;
            j = end + 2;
            continue;
          } else {
            j = line.length;
            continue;
          }
        }
      }
      
      newLine += char;
      j++;
    }
    
    if (newLine.trim() || result.length === 0 || result[result.length - 1].trim()) {
      result.push(newLine);
    }
  }
  
  return result.join('\n');
}

function removeCSSComments(content) {
  return content.replace(/\/\*[^*]*\*+(?:[^/*][^*]*\*+)*\//g, '');
}

function removeVueComments(content) {
  content = content.replace(/<!--[\s\S]*?-->/g, '');
  
  content = content.replace(/(<script[^>]*>)([\s\S]*?)(<\/script>)/gi, (match, open, script, close) => {
    return open + removeTSJSComments(script) + close;
  });
  
  content = content.replace(/(<style[^>]*>)([\s\S]*?)(<\/style>)/gi, (match, open, style, close) => {
    return open + removeCSSComments(style) + close;
  });
  
  return content;
}

function processFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf8');
    const ext = path.extname(filePath);
    let newContent = content;
    
    if (ext === '.vue') {
      newContent = removeVueComments(content);
    } else if (['.ts', '.js', '.tsx', '.jsx'].includes(ext)) {
      newContent = removeTSJSComments(content);
    } else if (ext === '.css') {
      newContent = removeCSSComments(content);
    } else {
      return;
    }
    
    const lines = newContent.split('\n');
    const cleanedLines = [];
    let emptyCount = 0;
    for (const line of lines) {
      if (!line.trim()) {
        emptyCount++;
        if (emptyCount <= 2) {
          cleanedLines.push(line);
        }
      } else {
        emptyCount = 0;
        cleanedLines.push(line);
      }
    }
    newContent = cleanedLines.join('\n');
    
    if (newContent !== content) {
      fs.writeFileSync(filePath, newContent, 'utf8');
      console.log(`Processado: ${filePath}`);
    }
  } catch (error) {
    console.error(`Erro ao processar ${filePath}:`, error.message);
  }
}

function walkDir(dir, fileList = []) {
  const files = fs.readdirSync(dir);
  
  for (const file of files) {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      if (file !== 'node_modules' && file !== 'dist') {
        walkDir(filePath, fileList);
      }
    } else {
      const ext = path.extname(file);
      if (['.ts', '.js', '.tsx', '.jsx', '.vue', '.css'].includes(ext)) {
        fileList.push(filePath);
      }
    }
  }
  
  return fileList;
}

const srcDir = path.join(__dirname, 'src');
if (!fs.existsSync(srcDir)) {
  console.error('Diretório src não encontrado!');
  process.exit(1);
}

const files = walkDir(srcDir);

const rootFiles = ['vite.config.ts', 'eslint.config.ts'].map(f => path.join(__dirname, f));
for (const file of rootFiles) {
  if (fs.existsSync(file)) {
    files.push(file);
  }
}

for (const file of files) {
  processFile(file);
}

console.log('Concluído!');

