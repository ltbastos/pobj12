import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig(({ mode }) => {

  const env = loadEnv(mode, process.cwd())

  return {
    // Em dev: raiz (/)
    // Em produção: /dist/
    base: mode === 'production' ? '/pobj-vue/' : '/',

    plugins: [
      vue(),
      vueDevTools(),
    ],

    build: {
      outDir: path.resolve(__dirname, '../../public/dist'),
      emptyOutDir: true
    },

    define: {
      __API__: JSON.stringify(env.VITE_API_URL)
    },

    server: {
      proxy: {
        '/api': {
          target: env.VITE_API_URL || 'http://localhost/pobj-vue',
          changeOrigin: true,
          secure: false,
        }
      }
    }
  }
})
