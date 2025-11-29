<script setup lang="ts">
</script>

<template>
  <footer class="footer">
    <div class="footer__container">
      <div class="footer__logo">
        <span class="footer__logo-mark" aria-hidden="true"></span>
        <span class="footer__logo-text">Bradesco POBJ</span>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  width: 100%;
  margin-top: auto;
  padding: 24px 16px;
  background: var(--brad-color-neutral-0);
  border-top: 1px solid var(--brad-color-gray-light);
}

.footer__container {
  max-width: 1320px;
  margin: 0 auto;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.footer__logo {
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}

.footer__logo-mark {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--brad-color-primary-gradient);
  background-image: url('/img/bra-logo.png');
  background-size: 70%;
  background-position: center;
  background-repeat: no-repeat;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(204, 9, 47, 0.2);
}

.footer__logo-text {
  font-family: var(--brad-font-family);
  font-size: 16px;
  font-weight: var(--brad-font-weight-bold);
  color: var(--brad-color-neutral-100);
  letter-spacing: -0.02em;
}

@media (max-width: 767px) {
  .footer {
    padding: 20px 16px;
  }

  .footer__logo-text {
    font-size: 14px;
  }

  .footer__logo-mark {
    width: 28px;
    height: 28px;
  }
}
</style>


