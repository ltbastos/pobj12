<script setup lang="ts">
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'
import Filters from './components/Filters.vue'
import TabsNavigation from './components/TabsNavigation.vue'
import OmegaLegacyModal from './components/OmegaLegacyModal.vue'
import { useRoute } from 'vue-router'
import './assets/bradesco-theme.css'
import './assets/animations.css'
import './assets/microinteractions.css'
import './assets/accessibility.css'

const route = useRoute()
</script>

<template>
  <div class="app">
    <a href="#main-content" class="skip-link">Pular para conteúdo principal</a>
    <a href="#main-navigation" class="skip-link">Pular para navegação</a>
    
    <Header />
    <main id="main-content" class="main-content" role="main" aria-label="Conteúdo principal">
      <div class="main-container">
        <Filters />
        <TabsNavigation />
        
        <div class="view-content">
          <router-view v-slot="{ Component }">
            <Transition
              name="page"
              mode="out-in"
              appear
            >
              <component :is="Component" :key="route.path" />
            </Transition>
          </router-view>
        </div>
      </div>
    </main>
    <Footer />
    <OmegaLegacyModal />
  </div>
</template>

<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html,
body {
  margin: 0;
  padding: 0;
  width: 100%;
  overflow-x: hidden;
  font-family: "Bradesco", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

#app {
  width: 100%;
  margin: 0;
  padding: 0;
  font-family: "Bradesco", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
}

.ti {
  font-family: 'tabler-icons' !important;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

body {
  background-color: #f6f7fc;
  background-image:
    url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%20viewBox%3D%270%200%20320%20320%27%3E%3Ctext%20x%3D%2750%25%27%20y%3D%2750%25%27%20fill%3D%27rgba%2815%2C20%2C36%2C0.08%29%27%20font-size%3D%2720%27%20font-family%3D%27Plus%20Jakarta%20Sans%2C%20sans-serif%27%20text-anchor%3D%27middle%27%20dominant-baseline%3D%27middle%27%20transform%3D%27rotate%28-30%20160%20160%29%27%3EX%20Burguer%20%E2%80%A2%20Funcional%201234567%3C/text%3E%3C/svg%3E"),
    radial-gradient(1200px 720px at 95% -30%, #dfe8ff 0%, transparent 60%),
    radial-gradient(1100px 720px at -25% -10%, #ffe6ea 0%, transparent 55%);
  background-repeat: repeat, no-repeat, no-repeat;
  background-size: 320px 320px, auto, auto;
  background-position: center center, 95% -30%, -25% -10%;
  background-attachment: fixed;
}
</style>

<style scoped>
.app {
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  margin: 0;
  padding: 0;
}

.main-content {
  flex: 1;
  width: 100%;
  padding-top: 66px; /* Altura do header fixo */
}

.main-container {
  max-width: min(1600px, 96vw);
  margin: 18px auto;
  padding: 0 16px;
}

.view-content {
  width: 100%;
}

.page-enter-active {
  transition: all 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.page-leave-active {
  transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.page-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
