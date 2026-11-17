<template>
  <Transition name="chat-widget-slide" appear>
    <div
      ref="dialogRef"
      class="chat-widget__dialog"
      :class="{ 'chat-widget__dialog--form': phase !== 'chat' }"
      role="dialog"
      aria-modal="true"
      :aria-label="t.title"
      @keydown.esc="$emit('close')"
    >
      <!-- Header -->
      <div class="chat-widget__header">
        <div class="chat-widget__header-info">
          <div class="chat-widget__avatar" aria-hidden="true">A</div>
          <div>
            <div class="chat-widget__agent-name">{{ t.agentName }}</div>
            <div class="chat-widget__status" :class="`chat-widget__status--${availStatus}`">
              <span class="chat-widget__status-dot" aria-hidden="true"></span>
              {{ statusText }}
            </div>
          </div>
        </div>
        <div class="chat-widget__header-actions">
          <button class="chat-widget__close-btn" :aria-label="t.close" @click="$emit('close')">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Phase: form -->
      <div v-if="phase === 'form'" class="chat-widget__form-wrap">
        <p class="chat-widget__form-title">{{ t.formTitle }}</p>

        <div v-if="nextOpenText" class="chat-widget__out-hours">{{ nextOpenText }}</div>

        <form class="chat-widget__form" novalidate @submit.prevent="submitForm">
          <!-- Honeypot -->
          <input
            v-model="honeypot"
            type="text"
            name="website"
            tabindex="-1"
            autocomplete="off"
            class="chat-widget__honeypot"
            aria-hidden="true"
          />

          <div class="chat-widget__field">
            <label :for="uid('name')" class="chat-widget__label">{{ t.nameLabel }}</label>
            <input
              :id="uid('name')"
              v-model="formName"
              type="text"
              class="chat-widget__input"
              :class="{ 'chat-widget__input--error': formErrors.name }"
              :placeholder="t.namePlaceholder"
              autocomplete="name"
              maxlength="100"
              required
            />
            <span v-if="formErrors.name" class="chat-widget__error" role="alert">{{ formErrors.name }}</span>
          </div>

          <div class="chat-widget__field">
            <label :for="uid('phone')" class="chat-widget__label">{{ t.phoneLabel }}</label>
            <input
              :id="uid('phone')"
              v-model="formPhone"
              type="tel"
              class="chat-widget__input"
              :placeholder="t.phonePlaceholder"
              autocomplete="tel"
              maxlength="30"
            />
          </div>

          <div class="chat-widget__field">
            <label :for="uid('email')" class="chat-widget__label">{{ t.emailLabel }}</label>
            <input
              :id="uid('email')"
              v-model="formEmail"
              type="email"
              class="chat-widget__input"
              :placeholder="t.emailPlaceholder"
              autocomplete="email"
              maxlength="254"
            />
          </div>

          <div class="chat-widget__field chat-widget__field--consent">
            <label class="chat-widget__consent">
              <input
                v-model="formConsent"
                type="checkbox"
                class="chat-widget__checkbox"
                :class="{ 'chat-widget__checkbox--error': formErrors.consent }"
                required
              />
              <span>
                {{ t.consent }}
                <a :href="privacyUrl" target="_blank" rel="noopener" class="chat-widget__link">{{ t.privacyLink }}</a>
              </span>
            </label>
            <span v-if="formErrors.consent" class="chat-widget__error" role="alert">{{ formErrors.consent }}</span>
          </div>

          <button
            type="submit"
            class="chat-widget__btn chat-widget__btn--primary"
            :disabled="submitting"
          >
            {{ submitting ? t.uploading : t.startChat }}
          </button>
        </form>
      </div>

      <!-- Phase: new-conversation offer -->
      <div v-else-if="phase === 'new-conv'" class="chat-widget__form-wrap">
        <p class="chat-widget__form-title">{{ t.newConv }}</p>
        <p class="chat-widget__new-conv-text">{{ t.newConvPrompt }}</p>
        <button class="chat-widget__btn chat-widget__btn--primary" @click="resetConversation">
          {{ t.newConv }}
        </button>
      </div>

      <!-- Phase: chat -->
      <template v-else-if="phase === 'chat'">
        <div
          ref="messagesRef"
          class="chat-widget__messages"
          aria-live="polite"
          aria-atomic="false"
          aria-relevant="additions"
        >
          <div
            v-for="msg in messages"
            :key="msg.id ?? msg._lid"
            class="chat-widget__message"
            :class="`chat-widget__message--${msg.sender}`"
          >
            <template v-if="msg.sender === 'system'">
              <span class="chat-widget__system-msg">{{ msg.body }}</span>
            </template>
            <template v-else>
              <div class="chat-widget__bubble">
                <span v-if="msg.body" class="chat-widget__bubble-text">{{ msg.body }}</span>
                <img
                  v-if="msg.attachment_url"
                  :src="msg.attachment_url"
                  class="chat-widget__bubble-img"
                  alt=""
                  loading="lazy"
                />
                <div class="chat-widget__bubble-meta">
                  <time :datetime="msg.created_at" class="chat-widget__time">
                    {{ formatTime(msg.created_at) }}
                  </time>
                  <span
                    v-if="msg.sender === 'visitor'"
                    class="chat-widget__checks"
                    :class="{ 'chat-widget__checks--read': !!msg.read_at }"
                    :aria-label="msg.read_at ? t.read : t.sent"
                  >
                    <svg viewBox="0 0 18 18" aria-hidden="true">
                      <path d="M17.394 5.035l-.57-.444a.434.434 0 00-.609.076L8.397 15.11l-3.962-4.012a.434.434 0 00-.617 0l-.522.52a.44.44 0 000 .617l4.825 4.885 9.273-11.48a.434.434 0 00-.076-.609z"/>
                      <path d="M13.398 5.035l-.57-.444a.434.434 0 00-.609.076L6.5 12.09l-.32-.323-1.14 1.41 1.46 1.478 7.898-9.012a.434.434 0 00-.076-.609z" class="chat-widget__check2"/>
                    </svg>
                  </span>
                  <button
                    v-if="msg._failed"
                    class="chat-widget__retry"
                    @click="retryMessage(msg)"
                  >{{ t.retry }}</button>
                </div>
              </div>
            </template>
          </div>

          <!-- Typing indicator -->
          <div v-if="agentTyping" class="chat-widget__message chat-widget__message--agent">
            <div class="chat-widget__bubble chat-widget__bubble--typing">
              <span></span><span></span><span></span>
            </div>
          </div>
        </div>

        <!-- Attachment preview -->
        <div v-if="attachPreview" class="chat-widget__attachment-preview">
          <img :src="attachPreview" alt="" class="chat-widget__preview-img" />
          <button class="chat-widget__preview-remove" :aria-label="t.removePhoto" @click="clearAttachment">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
          </button>
        </div>

        <!-- Upload progress -->
        <div v-if="uploadProgress > 0 && uploadProgress < 100" class="chat-widget__progress">
          <div class="chat-widget__progress-bar" :style="{ width: uploadProgress + '%' }"></div>
        </div>

        <!-- Input area -->
        <div class="chat-widget__input-area">
          <label class="chat-widget__attach-btn" :aria-label="t.attach">
            <input
              ref="fileInputRef"
              type="file"
              accept="image/*"
              class="chat-widget__file-input"
              @change="onFileSelected"
            />
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
            </svg>
          </label>
          <textarea
            ref="textareaRef"
            v-model="inputText"
            class="chat-widget__textarea"
            :placeholder="t.inputPlaceholder"
            rows="1"
            maxlength="2000"
            @keydown.enter.exact.prevent="sendMessage"
            @input="onTextareaInput"
          ></textarea>
          <button
            class="chat-widget__send-btn"
            :disabled="!canSend || sending"
            :aria-label="t.send"
            @click="sendMessage"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
          </button>
        </div>
      </template>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { detectLocale, getMessages as getLocaleMessages } from './locale.js'
import { getConversation, saveConversation, clearConversation, updateLastActivity } from './storage.js'
import { getAvailability, createConversation, getMessages, sendMessage as apiSendMessage, markRead, sendTyping } from './api.js'
import { resizeImage } from './imageResize.js'

// ── Props / emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  initialUuid:   { type: String,  default: null },
  initialToken:  { type: String,  default: null },
  initialUnread: { type: Number,  default: 0 },
})

const emit = defineEmits(['close', 'unread-update', 'activity'])

// ── Constants ─────────────────────────────────────────────────────────────────
const WHATSAPP_URL    = import.meta.env.VITE_WHATSAPP_URL || 'https://wa.me/34678758210'
const PRIVACY_URL     = '/politica-de-privacidad'
const POLL_ACTIVE     = 3000
const POLL_BACKGROUND = 20000
const BACKOFF_MAX     = 30000
const TYPING_THROTTLE = 3000
let   _uidCounter     = 0

// ── State ─────────────────────────────────────────────────────────────────────
const locale      = detectLocale()
const t           = getLocaleMessages(locale)
const whatsappUrl = WHATSAPP_URL
const privacyUrl  = PRIVACY_URL

const phase = ref('form')   // 'form' | 'chat' | 'new-conv'

// Availability
const availStatus  = ref('online')
const nextOpenText = ref('')

// Form
const formName    = ref('')
const formPhone   = ref('')
const formEmail   = ref('')
const formConsent = ref(false)
const honeypot    = ref('')
const formErrors  = ref({})
const submitting  = ref(false)

// Chat
const messages       = ref([])
const inputText      = ref('')
const sending        = ref(false)
const agentTyping    = ref(false)
const uploadProgress = ref(0)
const attachFile     = ref(null)
const attachPreview  = ref(null)
let   lastMessageId  = 0
let   pollTimer      = null
let   backoffMs      = POLL_ACTIVE
let   errorCount     = 0
let   originalTitle  = document.title
let   lastTypingSent = 0

// Conversation
const conversationUuid  = ref(props.initialUuid)
const conversationToken = ref(props.initialToken)

const dialogRef    = ref(null)
const messagesRef  = ref(null)
const textareaRef  = ref(null)
const fileInputRef = ref(null)

// ── Computed ─────────────────────────────────────────────────────────────────
const unreadCount = computed(() =>
  messages.value.filter(m => m.sender === 'agent' && !m.read_at).length,
)

const canSend = computed(() =>
  (inputText.value.trim().length > 0 || attachFile.value !== null) && !sending.value,
)

const statusText = computed(() => {
  if (availStatus.value === 'online') return t.online
  if (availStatus.value === 'away')   return t.away
  return t.closed
})

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  setupVisualViewport()
  document.addEventListener('visibilitychange', onVisibilityChange)
  document.addEventListener('keydown', onDocKeydown)

  // Use props or fall back to localStorage
  if (!conversationUuid.value || !conversationToken.value) {
    const saved = getConversation()
    if (saved) {
      conversationUuid.value  = saved.uuid
      conversationToken.value = saved.token
    }
  }

  await refreshAvailability()

  if (conversationUuid.value && conversationToken.value) {
    await loadSavedConversation()
  }

  trapFocus()
})

onUnmounted(() => {
  stopPoll()
  document.removeEventListener('visibilitychange', onVisibilityChange)
  document.removeEventListener('keydown', onDocKeydown)
  if (attachPreview.value) URL.revokeObjectURL(attachPreview.value)
  restoreTitle()
})

// ── Availability ──────────────────────────────────────────────────────────────
async function refreshAvailability() {
  try {
    const data = await getAvailability(locale)
    if (data.is_online)       availStatus.value = 'online'
    else if (data.is_open)    availStatus.value = 'away'
    else                      availStatus.value = 'closed'
    nextOpenText.value = data.next_open_text || ''
  } catch { /* noop */ }
}

// ── Saved conversation restore ────────────────────────────────────────────────
async function loadSavedConversation() {
  try {
    const data = await getMessages(conversationUuid.value, conversationToken.value, 0)
    if (data.status === 'closed') {
      phase.value = 'new-conv'
      return
    }
    messages.value = data.messages || []
    lastMessageId  = messages.value.at(-1)?.id ?? 0
    agentTyping.value = !!data.agent_typing
    phase.value = 'chat'
    await nextTick()
    scrollBottom()
    // Mark all as read since we just opened the dialog
    await markRead(conversationUuid.value, conversationToken.value).catch(() => {})
    emit('unread-update', 0)
    startPoll()

    const latest = messages.value.at(-1)
    if (latest?.created_at) emit('activity', latest.created_at)
  } catch (err) {
    if (err.response?.status === 401 || err.response?.status === 404) {
      clearConversation()
      conversationUuid.value  = null
      conversationToken.value = null
      phase.value = 'new-conv'
    }
  }
}

// ── Form submit ───────────────────────────────────────────────────────────────
async function submitForm() {
  formErrors.value = {}

  if (honeypot.value !== '') return

  if (!formName.value.trim()) {
    formErrors.value.name = t.nameRequired
    return
  }
  if (!formConsent.value) {
    formErrors.value.consent = t.consentRequired
    return
  }

  submitting.value = true
  try {
    const result = await createConversation({
      name:    formName.value.trim(),
      phone:   formPhone.value.trim() || null,
      email:   formEmail.value.trim() || null,
      locale,
      pageUrl: window.location.href,
    })

    conversationUuid.value  = result.uuid
    conversationToken.value = result.visitor_token
    saveConversation(result.uuid, result.visitor_token)

    messages.value = []
    lastMessageId  = 0
    phase.value    = 'chat'
    await nextTick()
    scrollBottom()
    startPoll()
  } catch (err) {
    const errs = err.response?.data?.errors
    if (errs) {
      formErrors.value = {
        name:    errs.visitor_name?.[0],
        consent: errs.consent?.[0],
      }
    }
  } finally {
    submitting.value = false
  }
}

// ── Send message ──────────────────────────────────────────────────────────────
async function sendMessage() {
  if (!canSend.value) return

  const body = inputText.value.trim()
  const file = attachFile.value

  inputText.value  = ''
  attachFile.value = null
  if (attachPreview.value) {
    URL.revokeObjectURL(attachPreview.value)
    attachPreview.value = null
  }
  uploadProgress.value = 0

  const lid = ++_uidCounter
  const optimistic = {
    _lid: lid, id: null, sender: 'visitor', body,
    attachment_url: file ? URL.createObjectURL(file) : null,
    created_at: new Date().toISOString(), read_at: null, _failed: false,
  }
  messages.value.push(optimistic)
  await nextTick()
  scrollBottom()

  sending.value = true
  try {
    const result = await apiSendMessage(
      conversationUuid.value,
      conversationToken.value,
      body,
      file,
      (evt) => { if (evt.total) uploadProgress.value = Math.round(evt.loaded / evt.total * 100) },
    )
    const idx = messages.value.findIndex(m => m._lid === lid)
    if (idx !== -1) messages.value[idx] = result
    lastMessageId = Math.max(lastMessageId, result.id)
    emit('activity', result.created_at)
    updateLastActivity(result.created_at)
  } catch {
    const idx = messages.value.findIndex(m => m._lid === lid)
    if (idx !== -1) messages.value[idx]._failed = true
  } finally {
    sending.value        = false
    uploadProgress.value = 0
  }
}

async function retryMessage(msg) {
  msg._failed = false
  const body = msg.body
  const idx  = messages.value.findIndex(m => m._lid === msg._lid)
  if (idx !== -1) messages.value.splice(idx, 1)
  inputText.value = body
  await sendMessage()
}

// ── Polling ───────────────────────────────────────────────────────────────────
function startPoll() {
  stopPoll()
  if (phase.value !== 'chat') return
  backoffMs = document.hidden ? POLL_BACKGROUND : POLL_ACTIVE
  pollTimer = setTimeout(poll, backoffMs)
}

function stopPoll() {
  if (pollTimer) { clearTimeout(pollTimer); pollTimer = null }
}

async function poll() {
  if (!conversationUuid.value || !conversationToken.value) return

  try {
    const data = await getMessages(conversationUuid.value, conversationToken.value, lastMessageId)
    errorCount = 0

    if (data.status === 'closed' && messages.value.at(-1)?.sender !== 'system') {
      messages.value.push({ _lid: ++_uidCounter, sender: 'system', body: t.closed_conv })
    }

    if (data.messages?.length > 0) {
      messages.value.push(...data.messages)
      const latest = data.messages.at(-1)
      lastMessageId = latest.id
      emit('activity', latest.created_at)
      updateLastActivity(latest.created_at)

      if (!document.hidden) {
        await markRead(conversationUuid.value, conversationToken.value).catch(() => {})
        await nextTick()
        scrollBottom()
        data.messages.forEach(m => { if (m.sender === 'agent') m.read_at = new Date().toISOString() })
        emit('unread-update', 0)
      } else {
        emit('unread-update', unreadCount.value)
        updateTabTitle()
      }
    }

    agentTyping.value = !!data.agent_typing
    backoffMs = document.hidden ? POLL_BACKGROUND : POLL_ACTIVE
  } catch (err) {
    if (err.response?.status === 401 || err.response?.status === 404) {
      stopPoll()
      clearConversation()
      phase.value = 'new-conv'
      return
    }
    errorCount++
    backoffMs = Math.min(POLL_ACTIVE * Math.pow(2, errorCount), BACKOFF_MAX)
  }

  pollTimer = setTimeout(poll, backoffMs)
}

function onVisibilityChange() {
  if (document.hidden) {
    stopPoll()
    if (phase.value === 'chat') pollTimer = setTimeout(poll, POLL_BACKGROUND)
  } else {
    restoreTitle()
    stopPoll()
    if (phase.value === 'chat') startPoll()
  }
}

// ── Tab title ─────────────────────────────────────────────────────────────────
function updateTabTitle() {
  const count = unreadCount.value
  if (count > 0) document.title = `(${count}) ${originalTitle}`
}

function restoreTitle() {
  document.title = originalTitle
}

// ── File handling ──────────────────────────────────────────────────────────────
async function onFileSelected(evt) {
  const file = evt.target.files?.[0]
  if (!fileInputRef.value) return
  fileInputRef.value.value = ''
  if (!file) return
  try {
    const resized = await resizeImage(file)
    attachFile.value = resized
    if (attachPreview.value) URL.revokeObjectURL(attachPreview.value)
    attachPreview.value = URL.createObjectURL(resized)
  } catch { /* ignore */ }
}

function clearAttachment() {
  attachFile.value = null
  if (attachPreview.value) { URL.revokeObjectURL(attachPreview.value); attachPreview.value = null }
}

// ── Textarea ───────────────────────────────────────────────────────────────────
function onTextareaInput() {
  const el = textareaRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 120) + 'px'

  // Throttled typing indicator: max 1 call per TYPING_THROTTLE ms
  if (!conversationUuid.value || !conversationToken.value) return
  const now = Date.now()
  if (now - lastTypingSent < TYPING_THROTTLE) return
  lastTypingSent = now
  sendTyping(conversationUuid.value, conversationToken.value).catch(() => {})
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function scrollBottom() {
  const el = messagesRef.value
  if (el) el.scrollTop = el.scrollHeight
}

function resetConversation() {
  clearConversation()
  conversationUuid.value  = null
  conversationToken.value = null
  messages.value     = []
  lastMessageId      = 0
  formName.value     = ''
  formPhone.value    = ''
  formEmail.value    = ''
  formConsent.value  = false
  formErrors.value   = {}
  phase.value        = 'form'
}

function formatTime(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' })
}

function uid(suffix) {
  return `cw-${suffix}-${_uidCounter}`
}

// ── Focus trap ────────────────────────────────────────────────────────────────
let focusTrapHandler = null
let previousFocus    = null

function trapFocus() {
  previousFocus = document.activeElement
  const el = dialogRef.value
  if (!el) return
  const focusable = el.querySelectorAll(
    'a[href],button:not([disabled]),textarea,input,select,[tabindex]:not([tabindex="-1"])',
  )
  focusable[0]?.focus()
  focusTrapHandler = (e) => {
    if (e.key !== 'Tab') return
    const items = [...focusable].filter(n => !n.closest('[aria-hidden="true"]'))
    if (items.length === 0) return
    const first = items[0]; const last = items[items.length - 1]
    if (e.shiftKey) {
      if (document.activeElement === first) { e.preventDefault(); last.focus() }
    } else {
      if (document.activeElement === last) { e.preventDefault(); first.focus() }
    }
  }
  document.addEventListener('keydown', focusTrapHandler)
}

function onDocKeydown(e) {
  if (e.key === 'Escape') emit('close')
}

// ── iOS/Android visual viewport (teclado virtual) ────────────────────────────
// En móvil, `position:fixed; bottom:0` se ancla al layout viewport (incluye el
// área del teclado). Cuando aparece el teclado el diálogo queda tapado.
// Fix: detectar la altura del teclado y aplicar bottom+height directamente
// al elemento, sobreescribiendo el CSS solo cuando el teclado está visible.
function setupVisualViewport() {
  if (!window.visualViewport) return
  const update = () => {
    const vv  = window.visualViewport
    const el  = dialogRef.value
    if (!el) return

    // Solo en pantallas estrechas (móvil)
    if (window.innerWidth > 480) {
      el.style.bottom    = ''
      el.style.height    = ''
      el.style.maxHeight = ''
      return
    }

    // Altura del teclado = diferencia entre layout viewport y visual viewport
    const kbHeight = Math.max(0, window.innerHeight - vv.offsetTop - vv.height)

    // Desplazar el diálogo por encima del teclado
    el.style.bottom = kbHeight + 'px'

    if (kbHeight > 0) {
      // Teclado visible: ajustar altura al espacio disponible
      el.style.height    = vv.height + 'px'
      el.style.maxHeight = vv.height + 'px'
    } else {
      // Sin teclado: dejar que el CSS controle altura (auto en form, 100svh en chat)
      el.style.height    = ''
      el.style.maxHeight = ''
    }
  }
  window.visualViewport.addEventListener('resize', update)
  window.visualViewport.addEventListener('scroll', update)
  update()
}

watch(phase, async (val) => {
  if (val === 'chat') {
    await nextTick(); scrollBottom(); startPoll()
  } else {
    stopPoll()
  }
})

watch(unreadCount, (val) => emit('unread-update', val))
</script>
