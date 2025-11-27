import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig(({ mode }) => {

  const env = loadEnv(mode, process.cwd())

  return {
    // Base path para rodar no XAMPP dentro da pasta pobj-slim
    base: mode === 'production' ? '/pobj-slim/' : '/',

    plugins: [
      vue(),
      vueDevTools(),
    ],

    build: {
      // Gerar diretamente em public/ (sem pasta dist/)
      outDir: path.resolve(__dirname, '../../public'),
      // Limpar apenas arquivos do build (assets/, index.html, favicon.ico)
      // Não apaga index.php e outros arquivos importantes
      emptyOutDir: false,
      rollupOptions: {
        output: {
          // Manter assets na pasta assets/
          assetFileNames: 'assets/[name].[hash].[ext]',
          chunkFileNames: 'assets/[name].[hash].js',
          entryFileNames: 'assets/[name].[hash].js'
        }
      }
    },

    define: {
      __API__: JSON.stringify(env.VITE_API_URL)
    },

    server: {
      proxy: {
        '/api': {
          target: env.VITE_API_URL || 'http://localhost/pobj-slim',
          changeOrigin: true,
          secure: false,
        }
      }
    }
  }
})
