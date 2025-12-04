import { globalIgnores } from 'eslint/config'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import pluginVue from 'eslint-plugin-vue'
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting'

export default defineConfigWithVueTs(
  {
    name: 'app/files-to-lint',
    files: ['**/*.{ts,mts,tsx,vue}'],
  },

  globalIgnores(['**/dist/**', '**/dist-ssr/**', '**/coverage/**', '**/public/legacy/**', '**/env.d.ts']),

  pluginVue.configs['flat/essential'],
  vueTsConfigs.recommended,
  skipFormatting,
  
  {
    rules: {
      
      'vue/multi-word-component-names': ['error', {
        ignores: ['Button', 'Header', 'Footer', 'Filters', 'Toast', 'Home', 'Ranking', 'Detalhes', 'Campanhas', 'Simuladores']
      }],
      
      '@typescript-eslint/no-unused-vars': ['error', {
        argsIgnorePattern: '^_',
        varsIgnorePattern: '^_'
      }]
    }
  },
  {
    files: ['**/views/Campanhas.vue', '**/views/Detalhes.vue', '**/views/Ranking.vue', '**/views/Simuladores.vue'],
    rules: {
      
      '@typescript-eslint/no-explicit-any': 'off'
    }
  }
)
