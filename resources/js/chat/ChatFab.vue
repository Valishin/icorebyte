<template>
  <div class="chat-widget" :class="{ 'chat-widget--open': dialogOpen }">
    <!-- Chat FAB -->
    <button
      class="chat-widget__fab chat-widget__fab--chat"
      :aria-label="dialogOpen ? t.close : t.open"
      :aria-expanded="dialogOpen"
      @click="toggleDialog"
    >
      <span v-if="unreadCount > 0 && !dialogOpen" class="chat-widget__badge" aria-hidden="true">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
      <svg v-if="!dialogOpen" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
      </svg>
      <svg v-else viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
      </svg>
    </button>

    <!-- Dialog loaded on demand -->
    <component
      :is="dialogComponent"
      v-if="dialogComponent && dialogOpen"
      :initial-uuid="savedUuid"
      :initial-token="savedToken"
      :initial-unread="unreadCount"
      @close="closeDialog"
      @unread-update="onUnreadUpdate"
      @activity="onActivity"
    />
  </div>
</template>

<script setup>
import { ref, shallowRef, onMounted, onUnmounted } from 'vue'
import { detectLocale, getMessages as getLocaleMessages } from './locale.js'
import { getConversation, saveConversation, updateLastActivity, clearConversation, parseHashToken } from './storage.js'
import { getMessages } from './api.js'

const POLL_BACKGROUND = 20000
const ACTIVITY_MAX_AGE_MS = 24 * 60 * 60 * 1000  // 24 hours

const locale = detectLocale()
const t = getLocaleMessages(locale)

const dialogOpen      = ref(false)
const dialogComponent = shallowRef(null)
const unreadCount     = ref(0)

let savedUuid  = null
let savedToken = null
let bgPollTimer = null

// ── Mount ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
  // Check URL fragment for resume token: #chat=<uuid>.<rawToken>
  // Fragment never reaches the server → stays out of logs and Referer headers.
  const parsed = parseHashToken(window.location.hash)
  if (parsed) {
    saveConversation(parsed.uuid, parsed.token)
    // Clean fragment without reload (history entry stays clean)
    history.replaceState(null, '', window.location.pathname + window.location.search)
  }

  const saved = getConversation()
  if (!saved) return

  savedUuid  = saved.uuid
  savedToken = saved.token

  // Background poll only if last activity < 24h
  if (isRecentActivity(saved.lastActivity)) {
    await backgroundPoll()
    // Open dialog immediately on resume (token came from #fragment)
    if (parsed) {
      await openDialog()
    }
  } else if (parsed) {
    // Resume link even without recent activity — open dialog
    await openDialog()
  }
})

onUnmounted(() => stopBgPoll())

// ── Helpers ───────────────────────────────────────────────────────────────────
function isRecentActivity(lastActivity) {
  if (!lastActivity) return false
  return Date.now() - new Date(lastActivity).getTime() < ACTIVITY_MAX_AGE_MS
}

// ── Background polling (badge only) ───────────────────────────────────────────
async function backgroundPoll() {
  if (!savedUuid || !savedToken) return
  try {
    const data = await getMessages(savedUuid, savedToken, 0)
    const unread = (data.messages || []).filter(m => m.sender === 'agent' && !m.read_at).length
    unreadCount.value = unread
    // Update activity timestamp from latest message
    const latest = (data.messages || []).at(-1)
    if (latest?.created_at) updateLastActivity(latest.created_at)
  } catch (err) {
    if (err.response?.status === 401 || err.response?.status === 404) {
      clearConversation()
      savedUuid  = null
      savedToken = null
      return
    }
  }
  bgPollTimer = setTimeout(backgroundPoll, POLL_BACKGROUND)
}

function stopBgPoll() {
  if (bgPollTimer) { clearTimeout(bgPollTimer); bgPollTimer = null }
}

// ── Dialog ────────────────────────────────────────────────────────────────────
async function openDialog() {
  stopBgPoll()
  if (!dialogComponent.value) {
    const { default: Comp } = await import('./ChatWidgetDialog.vue')
    dialogComponent.value = Comp
  }
  dialogOpen.value = true
}

function closeDialog() {
  dialogOpen.value = false
  // Resume background polling if conversation is still active
  const saved = getConversation()
  if (saved && isRecentActivity(saved.lastActivity)) {
    savedUuid  = saved.uuid
    savedToken = saved.token
    backgroundPoll()
  }
}

async function toggleDialog() {
  if (dialogOpen.value) {
    closeDialog()
  } else {
    await openDialog()
  }
}

function onUnreadUpdate(count) {
  unreadCount.value = count
}

function onActivity(isoTimestamp) {
  updateLastActivity(isoTimestamp)
}
</script>
