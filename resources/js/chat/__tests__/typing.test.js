import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

// Minimal throttle utility matching the widget's implementation
function makeThrottledTyping(throttleMs, sendFn) {
  let lastSent = 0
  return function throttledSend() {
    const now = Date.now()
    if (now - lastSent < throttleMs) return false
    lastSent = now
    sendFn()
    return true
  }
}

describe('typing throttle (widget side)', () => {
  beforeEach(() => { vi.useFakeTimers() })
  afterEach(() => { vi.useRealTimers() })

  it('sends on first keystroke', () => {
    const send = vi.fn()
    const throttled = makeThrottledTyping(3000, send)
    expect(throttled()).toBe(true)
    expect(send).toHaveBeenCalledTimes(1)
  })

  it('suppresses second call within throttle window', () => {
    const send = vi.fn()
    const throttled = makeThrottledTyping(3000, send)
    throttled()                                        // t=0 → sent
    vi.advanceTimersByTime(1000)
    expect(throttled()).toBe(false)                    // t=1s → suppressed
    expect(send).toHaveBeenCalledTimes(1)
  })

  it('allows call after throttle window expires', () => {
    const send = vi.fn()
    const throttled = makeThrottledTyping(3000, send)
    throttled()                                        // t=0 → sent
    vi.advanceTimersByTime(3001)
    expect(throttled()).toBe(true)                     // t=3001ms → allowed
    expect(send).toHaveBeenCalledTimes(2)
  })

  it('suppresses many rapid calls to only 1 send within window', () => {
    const send = vi.fn()
    const throttled = makeThrottledTyping(3000, send)
    // Simulate fast typing: 10 keystrokes at 100ms intervals
    for (let i = 0; i < 10; i++) {
      vi.advanceTimersByTime(100)
      throttled()
    }
    expect(send).toHaveBeenCalledTimes(1)
  })

  it('sends once per window over multiple windows', () => {
    const send = vi.fn()
    const throttled = makeThrottledTyping(3000, send)
    throttled()              // window 1 → sent
    vi.advanceTimersByTime(1500)
    throttled()              // suppressed
    vi.advanceTimersByTime(1600)  // past 3000ms total
    throttled()              // window 2 → sent
    vi.advanceTimersByTime(3001)
    throttled()              // window 3 → sent
    expect(send).toHaveBeenCalledTimes(3)
  })
})
