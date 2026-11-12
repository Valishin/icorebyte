import { describe, it, expect, vi } from 'vitest'
import { detectLocale, getMessages } from '../locale.js'

function withLang(lang, fn) {
  vi.stubGlobal('navigator', { language: lang })
  fn()
  vi.unstubAllGlobals()
}

describe('detectLocale', () => {
  it('detects es from es-ES', () => {
    withLang('es-ES', () => expect(detectLocale()).toBe('es'))
  })

  it('detects ca from ca-ES', () => {
    withLang('ca-ES', () => expect(detectLocale()).toBe('ca'))
  })

  it('detects en from en-GB', () => {
    withLang('en-GB', () => expect(detectLocale()).toBe('en'))
  })

  it('detects fr from fr-FR', () => {
    withLang('fr-FR', () => expect(detectLocale()).toBe('fr'))
  })

  it('falls back to es for unknown locale', () => {
    withLang('ja-JP', () => expect(detectLocale()).toBe('es'))
  })

  it('handles exact match ca', () => {
    withLang('ca', () => expect(detectLocale()).toBe('ca'))
  })
})

describe('getMessages', () => {
  it('returns Spanish messages', () => {
    const m = getMessages('es')
    expect(m.send).toBe('Enviar')
    expect(m.online).toBe('En línea ahora')
  })

  it('returns English messages', () => {
    const m = getMessages('en')
    expect(m.send).toBe('Send')
    expect(m.online).toBe('Online now')
  })

  it('falls back to es for unsupported locale', () => {
    const m = getMessages('de')
    expect(m.send).toBe('Enviar')
  })

  it('all locales have the same keys', () => {
    const keys = Object.keys(getMessages('es'))
    for (const locale of ['ca', 'en', 'fr']) {
      expect(Object.keys(getMessages(locale))).toEqual(keys)
    }
  })
})
