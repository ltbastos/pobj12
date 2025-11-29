<script setup lang="ts">
const goBack = (): void => {
  window.history.back()
}
</script>

<template>
  <div class="error-container">
    <div class="error-card">
      <div class="error-icon">
        <i class="ti ti-alert-circle"></i>
      </div>
      <h1 class="error-code">404</h1>
      <h2 class="error-title">Página não encontrada</h2>
      <p class="error-message">
        A página que você está procurando não existe ou foi movida.
        Verifique o endereço e tente novamente.
      </p>
      <div class="error-actions">
        <router-link to="/" class="btn btn-primary">
          <i class="ti ti-home"></i>
          Voltar para o início
        </router-link>
        <button @click="goBack" class="btn btn-secondary">
          <i class="ti ti-arrow-left"></i>
          Voltar
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.error-container {
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
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background-color: var(--bg);
  background-image:
    url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%20viewBox%3D%270%200%20320%20320%27%3E%3Ctext%20x%3D%2750%25%27%20y%3D%2750%25%27%20fill%3D%27rgba%2815%2C20%2C36%2C0.08%29%27%20font-size%3D%2720%27%20font-family%3D%27Plus%20Jakarta%20Sans%2C%20sans-serif%27%20text-anchor%3D%27middle%27%20dominant-baseline%3D%27middle%27%20transform%3D%27rotate%28-30%20160%20160%29%27%3EX%20Burguer%20%E2%80%A2%20Funcional%201234567%3C/text%3E%3C/svg%3E"),
    radial-gradient(1200px 720px at 95% -30%, #dfe8ff 0%, transparent 60%),
    radial-gradient(1100px 720px at -25% -10%, #ffe6ea 0%, transparent 55%);
  background-repeat: repeat, no-repeat, no-repeat;
  background-size: 320px 320px, auto, auto;
  background-position: center center, 95% -30%, -25% -10%;
  color: var(--text);
  font-family: "Plus Jakarta Sans", Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.error-card {
  background: var(--panel);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 48px 32px;
  border: 1px solid var(--stroke);
  max-width: 600px;
  width: 100%;
  text-align: center;
}

.error-code {
  font-size: 120px;
  font-weight: 800;
  line-height: 1;
  background: linear-gradient(90deg, #cc092f 40%, #b81570 90%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 16px;
}

.error-title {
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  margin: 0 0 12px;
}

.error-message {
  font-size: 16px;
  color: var(--muted);
  margin: 0 0 32px;
  line-height: 1.6;
}

.error-icon {
  font-size: 64px;
  color: var(--brand);
  margin-bottom: 24px;
  opacity: 0.8;
}

.error-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 15px;
  text-decoration: none;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
  font-family: inherit;
}

.btn-primary {
  background: linear-gradient(90deg, #cc092f 40%, #b81570 90%);
  color: #fff;
  box-shadow: 0 6px 16px rgba(204, 9, 47, 0.25);
}

.btn-primary:hover {
  background: linear-gradient(90deg, #b81570 40%, #cc092f 90%);
  box-shadow: 0 8px 20px rgba(204, 9, 47, 0.35);
  transform: translateY(-1px);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.14);
  color: var(--text);
  border: 1px solid var(--stroke);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.22);
  border-color: rgba(0, 0, 0, 0.15);
}

@media (max-width: 640px) {
  .error-code {
    font-size: 80px;
  }

  .error-title {
    font-size: 24px;
  }

  .error-card {
    padding: 32px 24px;
  }

  .error-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
