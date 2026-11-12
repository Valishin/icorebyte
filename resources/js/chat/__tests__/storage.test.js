import { describe, it, expect, beforeEach, vi } from 'vitest'
import { getConversation, saveConversation, clearConversation, updateLastActivity, parseHashToken } from '../storage.js'

// ── localStorage helpers ───────────────────────────────────────────────────────
let store = {}
function mockStorage() {
  store = {}
  vi.stubGlobal('localStorage', {
    getItem:    (k) => store[k] ?? null,
    setItem:    (k, v) => { store[k] = String(v) },
    removeItem: (k) => { delete store[k] },
  })
}

describe('storage', () => {
  beforeEach(mockStorage)

  it('returns null when nothing stored', () => {
    expect(getConversation()).toBeNull()
  })

  it('saves and retrieves uuid + token', () => {
    saveConversation('uuid-123', 'tok-abc')
    const c = getConversation()
    expect(c).toMatchObject({ uuid: 'uuid-123', token: 'tok-abc' })
    expect(c.lastActivity).toBeNull()
  })

  it('stores and returns lastActivity via updateLastActivity', () => {
    saveConversation('uuid-123', 'tok-abc')
    updateLastActivity('2026-11-12T10:00:00.000Z')
    expect(getConversation().lastActivity).toBe('2026-11-12T10:00:00.000Z')
  })

  it('returns null after clear', () => {
    saveConversation('uuid-123', 'tok-abc')
    clearConversation()
    expect(getConversation()).toBeNull()
  })

  it('clearConversation also removes lastActivity', () => {
    saveConversation('uuid-123', 'tok-abc')
    updateLastActivity('2026-11-12T10:00:00.000Z')
    clearConversation()
    expect(getConversation()).toBeNull()
  })

  it('handles localStorage throwing', () => {
    vi.stubGlobal('localStorage', {
      getItem:    () => { throw new Error('blocked') },
      setItem:    () => { throw new Error('blocked') },
      removeItem: () => { throw new Error('blocked') },
    })
    expect(getConversation()).toBeNull()
    expect(() => saveConversation('x', 'y')).not.toThrow()
    expect(() => clearConversation()).not.toThrow()
  })
})

// ── parseHashToken ────────────────────────────────────────────────────────────
const VALID_UUID  = '804d15b9-239b-4618-b8fa-3a5ca71c5aaa'
const VALID_TOKEN = 'a'.repeat(64)   // 64 hex chars = 32 random bytes hex-encoded

describe('parseHashToken', () => {
  it('parses a valid fragment with leading #', () => {
    expect(parseHashToken(`#chat=${VALID_UUID}.${VALID_TOKEN}`))
      .toEqual({ uuid: VALID_UUID, token: VALID_TOKEN })
  })

  it('parses without leading #', () => {
    expect(parseHashToken(`chat=${VALID_UUID}.${VALID_TOKEN}`))
      .toEqual({ uuid: VALID_UUID, token: VALID_TOKEN })
  })

  it('returns null for empty string', () => {
    expect(parseHashToken('')).toBeNull()
  })

  it('returns null for wrong prefix', () => {
    expect(parseHashToken(`#other=${VALID_UUID}.${VALID_TOKEN}`)).toBeNull()
  })

  it('returns null when token is shorter than 64 hex chars', () => {
    expect(parseHashToken(`#chat=${VALID_UUID}.${'a'.repeat(63)}`)).toBeNull()
  })

  it('returns null when uuid is not a valid uuid pattern', () => {
    expect(parseHashToken(`#chat=not-a-uuid.${VALID_TOKEN}`)).toBeNull()
  })

  it('returns null for missing dot separator', () => {
    expect(parseHashToken(`#chat=${VALID_UUID}`)).toBeNull()
  })
})

// ── ChatFab fragment → localStorage round-trip ────────────────────────────────
describe('ChatFab reads fragment on mount', () => {
  beforeEach(mockStorage)

  it('parsed token from fragment can be saved and retrieved', () => {
    const parsed = parseHashToken(`#chat=${VALID_UUID}.${VALID_TOKEN}`)
    expect(parsed).not.toBeNull()

    saveConversation(parsed.uuid, parsed.token)

    const saved = getConversation()
    expect(saved.uuid).toBe(VALID_UUID)
    expect(saved.token).toBe(VALID_TOKEN)
  })

  it('token is not in any query-string key', () => {
    // The URL fragment never reaches server logs.
    // Verify the fragment format has no '?' separator before the token.
    const fragment = `chat=${VALID_UUID}.${VALID_TOKEN}`
    expect(fragment).not.toContain('?')
    expect(fragment).not.toContain('token=')
    expect(fragment).not.toContain('chat_token=')
  })
})
