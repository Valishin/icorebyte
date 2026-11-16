const KEY_UUID     = 'chat_uuid'
const KEY_TOKEN    = 'chat_token'
const KEY_ACTIVITY = 'chat_last_activity'  // ISO timestamp of most recent message

function safeGet(key) {
  try {
    return localStorage.getItem(key)
  } catch {
    return null
  }
}

function safeSet(key, value) {
  try {
    localStorage.setItem(key, value)
    return true
  } catch {
    return false
  }
}

function safeRemove(key) {
  try {
    localStorage.removeItem(key)
  } catch { /* noop */ }
}

export function getConversation() {
  const uuid     = safeGet(KEY_UUID)
  const token    = safeGet(KEY_TOKEN)
  const lastActivity = safeGet(KEY_ACTIVITY)
  if (uuid && token) return { uuid, token, lastActivity: lastActivity || null }
  return null
}

export function saveConversation(uuid, token) {
  safeSet(KEY_UUID, uuid)
  safeSet(KEY_TOKEN, token)
}

export function updateLastActivity(isoTimestamp) {
  if (isoTimestamp) safeSet(KEY_ACTIVITY, isoTimestamp)
}

export function clearConversation() {
  safeRemove(KEY_UUID)
  safeRemove(KEY_TOKEN)
  safeRemove(KEY_ACTIVITY)
}

/**
 * Parse the URL fragment for a resume token.
 * Expected format: `#chat=<uuid>.<rawToken>`
 * Returns { uuid, token } or null.
 * The fragment never reaches the server so the token stays out of logs.
 */
export function parseHashToken(hash) {
  const match = (hash || '').match(/^#?chat=([0-9a-f-]{36})\.([0-9a-f]{64})$/)
  if (!match) return null
  return { uuid: match[1], token: match[2] }
}
