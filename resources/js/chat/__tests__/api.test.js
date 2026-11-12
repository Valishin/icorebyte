import { describe, it, expect, vi, beforeEach } from 'vitest'

// A single shared mock instance returned by every axios.create() call
const mockGet  = vi.fn()
const mockPost = vi.fn()
const mockInst = { get: mockGet, post: mockPost }

vi.mock('axios', () => ({
  default: { create: vi.fn(() => mockInst) },
}))

import axios from 'axios'
import { getAvailability, createConversation, getMessages, sendMessage, markRead, sendTyping } from '../api.js'

beforeEach(() => {
  mockGet.mockReset()
  mockPost.mockReset()
  axios.create.mockClear()
})

describe('getAvailability', () => {
  it('calls GET /availability with locale param', async () => {
    mockGet.mockResolvedValueOnce({ data: { is_online: true } })
    const result = await getAvailability('es')
    expect(mockGet).toHaveBeenCalledWith('/availability', { params: { locale: 'es' } })
    expect(result.is_online).toBe(true)
  })
})

describe('createConversation', () => {
  it('posts visitor_name, locale, consent and empty honeypot', async () => {
    mockPost.mockResolvedValueOnce({ data: { uuid: 'u1', visitor_token: 'tk1' } })
    await createConversation({ name: 'Ana', phone: '', locale: 'es', pageUrl: 'http://localhost/' })
    const [url, payload] = mockPost.mock.calls[0]
    expect(url).toBe('/conversations')
    expect(payload.visitor_name).toBe('Ana')
    expect(payload.locale).toBe('es')
    expect(payload.consent).toBe('1')
    expect(payload.website).toBe('')
  })

  it('passes X-Chat-Visitor-Token header when no token (unauthenticated)', async () => {
    mockPost.mockResolvedValueOnce({ data: { uuid: 'u1', visitor_token: 'tk1' } })
    await createConversation({ name: 'Ana', phone: null, locale: 'es', pageUrl: null })
    // createConversation calls client() with no token — header should not be set
    const headers = axios.create.mock.calls[0]?.[0]?.headers ?? {}
    expect(headers['X-Chat-Visitor-Token']).toBeUndefined()
  })
})

describe('getMessages', () => {
  it('passes after param', async () => {
    mockGet.mockResolvedValueOnce({ data: { messages: [] } })
    await getMessages('uuid1', 'secret', 42)
    expect(mockGet).toHaveBeenCalledWith(
      '/conversations/uuid1/messages',
      { params: { after: 42 } },
    )
  })

  it('passes X-Chat-Visitor-Token header', async () => {
    mockGet.mockResolvedValueOnce({ data: { messages: [] } })
    await getMessages('uuid1', 'my-token', 0)
    const headers = axios.create.mock.calls.at(-1)?.[0]?.headers ?? {}
    expect(headers['X-Chat-Visitor-Token']).toBe('my-token')
  })
})

describe('sendMessage', () => {
  it('appends body and file to FormData', async () => {
    mockPost.mockResolvedValueOnce({ data: { id: 1 } })
    const file = new File(['data'], 'img.jpg', { type: 'image/jpeg' })
    await sendMessage('uuid1', 'tok', 'hello', file)
    const [url, form] = mockPost.mock.calls[0]
    expect(url).toBe('/conversations/uuid1/messages')
    expect(form).toBeInstanceOf(FormData)
    expect(form.get('body')).toBe('hello')
    expect(form.get('attachment')).toBe(file)
  })
})

describe('markRead', () => {
  it('posts to read endpoint', async () => {
    mockPost.mockResolvedValueOnce({ data: {} })
    await markRead('uuid1', 'tok')
    expect(mockPost).toHaveBeenCalledWith('/conversations/uuid1/read')
  })
})

describe('sendTyping', () => {
  it('posts to typing endpoint', async () => {
    mockPost.mockResolvedValueOnce({ data: {} })
    await sendTyping('uuid1', 'tok')
    expect(mockPost).toHaveBeenCalledWith('/conversations/uuid1/typing')
  })
})
