import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd())

  return {
    base: '/',
    plugins: [
      vue(),
      vueDevTools(),
      tailwindcss(),
    ],

    build: {
      outDir: 'dist',
      emptyOutDir: true,
      rollupOptions: {
        output: {
          assetFileNames: 'assets/[name].[hash].[ext]',
          chunkFileNames: 'assets/[name].[hash].js',
          entryFileNames: 'assets/[name].[hash].js'
        }
      }
    },

    resolve: {
      // Garante que arquivos CSS sejam resolvidos corretamente
      extensions: ['.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json', '.css']
    },

    optimizeDeps: {
      // Exclui pacotes que só contêm CSS da otimização de dependências
      exclude: ['@tabler/icons-webfont']
    },

    define: {
      __API__: JSON.stringify(env.VITE_API_URL)
    },

    server: {
      host: true,
      port: 5173,

      proxy: {
        '/api': {
          target: env.VITE_API_URL,
          changeOrigin: true,
          secure: false
        }
      }
    }
  }
})
