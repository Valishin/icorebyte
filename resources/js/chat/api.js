import axios from 'axios'

const BASE = '/api/chat'

function client(token = null) {
  const headers = { 'Accept': 'application/json' }
  if (token) headers['X-Chat-Visitor-Token'] = token
  return axios.create({ baseURL: BASE, headers })
}

export async function getAvailability(locale) {
  const { data } = await client().get('/availability', { params: { locale } })
  return data
}

export async function createConversation({ name, phone, email, locale, pageUrl }) {
  const payload = {
    visitor_name:  name,
    visitor_phone: phone || null,
    visitor_email: email || null,
    locale,
    page_url:      pageUrl,
    consent:       '1',
    website:       '',       // honeypot — must be empty
  }
  const { data } = await client().post('/conversations', payload)
  return data  // { uuid, visitor_token, is_online, next_open_text }
}

export async function getMessages(uuid, token, after = 0) {
  const { data } = await client(token).get(`/conversations/${uuid}/messages`, {
    params: { after },
  })
  return data  // { status, agent_typing, messages: [...] }
}

export async function sendMessage(uuid, token, body, attachmentFile = null, onUploadProgress = null) {
  const form = new FormData()
  if (body)           form.append('body', body)
  if (attachmentFile) form.append('attachment', attachmentFile)

  const cfg = {}
  if (onUploadProgress) cfg.onUploadProgress = onUploadProgress

  const { data } = await client(token).post(`/conversations/${uuid}/messages`, form, cfg)
  return data
}

export async function markRead(uuid, token) {
  await client(token).post(`/conversations/${uuid}/read`)
}

export async function sendTyping(uuid, token) {
  await client(token).post(`/conversations/${uuid}/typing`)
}
