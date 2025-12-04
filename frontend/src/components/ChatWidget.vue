<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { sendMessage } from '../services/agentService'
import Icon from './Icon.vue'

type Message = {
  role: 'user' | 'bot'
  text: string
  isTyping?: boolean
}

const isOpen = ref(false)
const messages = ref<Message[]>([])
const inputText = ref('')
const isSending = ref(false)

const messagesRef = ref<HTMLElement | null>(null)

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesRef.value) {
      messagesRef.value.scrollTop = messagesRef.value.scrollHeight
    }
  })
}

const addMessage = (role: 'user' | 'bot', text: string, isTyping = false) => {
  messages.value.push({ role, text, isTyping })
  scrollToBottom()
}

const inputRef = ref<HTMLInputElement | null>(null)

const toggleChat = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    nextTick(() => {
      inputRef.value?.focus()
    })
  }
}

const closeChat = () => {
  isOpen.value = false
}

const handleSubmit = async (e: Event) => {
  e.preventDefault()
  const question = inputText.value.trim()
  if (!question || isSending.value) return

  addMessage('user', question)
  inputText.value = ''
  isSending.value = true

  const typingIndex = messages.value.length
  addMessage('bot', '', true)

  try {
    const response = await sendMessage({ question })
    
    if (response && response.answer) {
      
      messages.value[typingIndex] = { role: 'bot', text: response.answer }
    } else {
      messages.value[typingIndex] = {
        role: 'bot',
        text: 'Desculpe, não consegui responder agora. Tente novamente.'
      }
    }
  } catch (error) {
    console.error('[Chat] Erro ao enviar mensagem:', error)
    messages.value[typingIndex] = {
      role: 'bot',
      text: error instanceof Error ? error.message : 'Falha ao falar com o agente. Tente novamente.'
    }
  } finally {
    isSending.value = false
    scrollToBottom()
  }
}

onMounted(() => {
  addMessage('bot', 'Olá! Posso ajudar com dúvidas sobre o POBJ e campanhas. O que você quer saber?')
})

const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isOpen.value) {
    closeChat()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <div class="chatw">
    <button
      id="chat-launcher"
      class="chatw__btn"
      type="button"
      aria-label="Abrir chat de dúvidas"
      @click="toggleChat"
    >
      <Icon name="message" :size="26" color="white" />
    </button>
    
    <section
      id="chat-panel"
      class="chatw__panel"
      :class="{ 'is-open': isOpen }"
      :aria-hidden="!isOpen"
      role="dialog"
      aria-label="Chat POBJ e campanhas"
    >
      <header class="chatw__header">
        <div class="chatw__title">Assistente POBJ &amp; Campanhas</div>
        <button
          id="chat-close"
          class="chatw__close"
          type="button"
          aria-label="Fechar chat"
          @click="closeChat"
        >
          <Icon name="x" :size="18" color="white" />
        </button>
      </header>

      <p class="chatw__disclaimer">
        Assistente virtual com IA — respostas podem conter erros.
      </p>

      <div ref="messagesRef" id="chat-messages" class="chatw__msgs" aria-live="polite">
        <div
          v-for="(msg, index) in messages"
          :key="index"
          class="msg"
          :class="[`msg--${msg.role}`, { 'msg--typing': msg.isTyping }]"
        >
          <div class="msg__bubble">
            <template v-if="msg.isTyping">
              <span class="dots">
                <i></i><i></i><i></i>
              </span>
            </template>
            <template v-else>
              {{ msg.text }}
            </template>
          </div>
        </div>
      </div>

      <form id="chat-form" class="chatw__form" autocomplete="off" @submit="handleSubmit">
        <input
          ref="inputRef"
          id="chat-input"
          v-model="inputText"
          type="text"
          placeholder="Pergunte sobre o POBJ ou campanhas…"
          :disabled="isSending"
          required
        />
        <button id="chat-send" type="submit" :disabled="isSending || !inputText.trim()">
          Enviar
        </button>
      </form>
    </section>
  </div>
</template>

<style scoped>

.chatw {
  position: fixed;
  right: 16px;
  bottom: 16px;
  z-index: 1400;
}

.chatw__btn {
  width: 58px;
  height: 58px;
  border-radius: 999px;
  border: 0;
  cursor: pointer;
  display: grid;
  place-items: center;
  box-shadow: 0 12px 28px rgba(17, 23, 41, 0.18);
  background: linear-gradient(135deg, #b30000, #d81e1e);
  color: #fff;
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}

.chatw__btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 36px rgba(17, 23, 41, 0.22);
  filter: brightness(1.03);
}

.chatw__btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(36, 107, 253, 0.18), 0 12px 28px rgba(17, 23, 41, 0.18);
}

.chatw__panel {
  position: absolute;
  right: 0;
  bottom: 74px;
  width: min(380px, 92vw);
  height: 520px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  box-shadow: 0 20px 46px rgba(2, 6, 23, 0.25);
  overflow: hidden;
  transform: translateY(8px) scale(0.98);
  opacity: 0;
  pointer-events: none;
  transition: transform 0.18s ease, opacity 0.18s ease;
  display: flex;
  flex-direction: column;
}

.chatw__panel.is-open {
  transform: translateY(0) scale(1);
  opacity: 1;
  pointer-events: auto;
}

.chatw__header {
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 0 12px;
  background: #fbfcff;
  border-bottom: 1px solid #eef2f7;
}

.chatw__title {
  font-weight: 800;
  color: #111827;
  font-size: 14px;
}

.chatw__close {
  border: 0;
  background: #b30000;
  border-radius: 8px;
  width: 30px;
  height: 28px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  transition: box-shadow 0.15s ease;
}

.chatw__close:hover {
  box-shadow: 0 6px 14px rgba(17, 23, 41, 0.08);
}

.chatw__disclaimer {
  margin: 0;
  padding: 6px 12px;
  font-size: 11px;
  color: #64748b;
  border-bottom: 1px solid #eef2f7;
  background: #f8fafc;
}

.chatw__msgs {
  flex: 1;
  height: calc(520px - 48px - 56px - 32px);
  padding: 12px;
  overflow: auto;
  background: #fafbff;
}

.chatw__form {
  height: 56px;
  display: flex;
  gap: 8px;
  align-items: center;
  padding: 8px;
  border-top: 1px solid #eef2f7;
  background: #fff;
}

#chat-input {
  flex: 1 1 auto;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px 12px;
  outline: none;
  font-size: 14px;
  font-family: inherit;
}

#chat-input:focus {
  box-shadow: 0 0 0 3px rgba(36, 107, 253, 0.12);
}

#chat-send {
  border: 0;
  background: #b30000;
  color: #fff;
  border-radius: 10px;
  padding: 10px 12px;
  font-weight: 700;
  cursor: pointer;
  font-size: 14px;
  font-family: inherit;
  transition: opacity 0.15s ease;
}

#chat-send:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.msg {
  display: flex;
  margin: 6px 0;
}

.msg--user {
  justify-content: flex-end;
}

.msg--bot {
  justify-content: flex-start;
}

.msg__bubble {
  max-width: 80%;
  padding: 10px 12px;
  border-radius: 14px;
  line-height: 1.25;
  font-size: 13.5px;
  box-shadow: 0 6px 14px rgba(17, 23, 41, 0.06);
}

.msg--user .msg__bubble {
  background: #e7f1ff;
  border: 1px solid #cfe3ff;
  color: #0f172a;
  border-top-right-radius: 6px;
}

.msg--bot .msg__bubble {
  background: #fff;
  border: 1px solid #e5e7eb;
  color: #111827;
  border-top-left-radius: 6px;
}

.msg--typing .dots {
  display: inline-block;
  width: 28px;
  height: 10px;
}

.msg--typing .dots i {
  display: inline-block;
  width: 6px;
  height: 6px;
  margin: 0 2px;
  border-radius: 999px;
  background: #9aa3b2;
  animation: bdot 1s infinite ease-in-out;
}

.msg--typing .dots i:nth-child(2) {
  animation-delay: 0.15s;
}

.msg--typing .dots i:nth-child(3) {
  animation-delay: 0.3s;
}

@keyframes bdot {
  0%,
  80%,
  100% {
    transform: translateY(0);
    opacity: 0.55;
  }
  40% {
    transform: translateY(-4px);
    opacity: 1;
  }
}

@media (max-width: 640px) {
  .chatw__panel {
    width: calc(100vw - 32px);
    right: 16px;
    left: 16px;
    max-width: none;
  }
}
</style>

