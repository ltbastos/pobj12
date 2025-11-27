import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import Home from '../views/Home.vue'
import Ranking from '../views/Ranking.vue'
import Campanhas from '../views/Campanhas.vue'
import Simuladores from '../views/Simuladores.vue'
import Detalhes from '../views/Detalhes.vue'
import VisaoExecutiva from '../views/VisaoExecutiva.vue'
import NotFound from '../views/NotFound.vue'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/detalhes',
    name: 'Detalhes',
    component: Detalhes,
    alias: ['/table']
  },
  {
    path: '/ranking',
    name: 'Ranking',
    component: Ranking
  },
  {
    path: '/campanhas',
    name: 'Campanhas',
    component: Campanhas
  },
  {
    path: '/simuladores',
    name: 'Simuladores',
    component: Simuladores
  },
  {
    path: '/exec',
    name: 'VisaoExecutiva',
    component: VisaoExecutiva
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound
  }
]

// Usar o mesmo base do Vite (import.meta.env.BASE_URL)
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

export default router
