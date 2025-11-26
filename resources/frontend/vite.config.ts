import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig(({ mode }) => {

  const env = loadEnv(mode, process.cwd())

  return {
    base: '/dist/',
    plugins: [vue()],
    build: {
      outDir: path.resolve(__dirname, '../../public/dist'),
      emptyOutDir: true
    },
    define: {
      __API__: JSON.stringify(env.VITE_API_URL)
    }
  }
})
