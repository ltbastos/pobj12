<script setup lang="ts">
import ResumoView from './ResumoView.vue'
</script>

<template>
  <div class="home-wrapper">
    <ResumoView />
  </div>
</template>

<style scoped>
.home-wrapper {
  --brand: #b30000;
  --brand-dark: #8f0000;
  --bg: #f6f7fc;
  --panel: #ffffff;
  --stroke: #e7eaf2;
  --text: #0f1424;
  --muted: #6b7280;
  --radius: 16px;
  --shadow: 0 12px 28px rgba(17, 23, 41, 0.08);

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  color: var(--text);
  font-family: var(--brad-font-family, inherit);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}


</style>

