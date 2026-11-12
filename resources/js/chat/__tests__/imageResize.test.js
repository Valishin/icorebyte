import { describe, it, expect, vi, beforeEach } from 'vitest'
import { resizeImage } from '../imageResize.js'

// Minimal canvas + Image mock
function makeCanvasMock(blob) {
  return {
    width: 0,
    height: 0,
    getContext: () => ({ drawImage: vi.fn() }),
    toBlob: vi.fn((cb) => cb(blob)),
  }
}

function makeImageMock(w, h) {
  const img = {
    onload: null,
    onerror: null,
    naturalWidth: w,
    naturalHeight: h,
    src: '',
  }
  // Trigger onload synchronously when src is set
  Object.defineProperty(img, 'src', {
    set(v) {
      this._src = v
      if (v && this.onload) this.onload()
    },
    get() { return this._src },
  })
  return img
}

beforeEach(() => {
  const blob = new Blob(['fake'], { type: 'image/jpeg' })
  const canvas = makeCanvasMock(blob)
  const img    = makeImageMock(3000, 2000)

  vi.stubGlobal('URL', {
    createObjectURL: vi.fn(() => 'blob:fake'),
    revokeObjectURL: vi.fn(),
  })
  vi.stubGlobal('Image', vi.fn(() => img))
  vi.stubGlobal('document', {
    createElement: vi.fn(() => canvas),
  })
})

describe('resizeImage', () => {
  it('returns a File named with .jpg extension', async () => {
    const file   = new File(['x'], 'photo.png', { type: 'image/png' })
    const result = await resizeImage(file)
    expect(result).toBeInstanceOf(File)
    expect(result.name).toBe('photo.jpg')
    expect(result.type).toBe('image/jpeg')
  })

  it('renames non-jpg files to .jpg', async () => {
    const file   = new File(['x'], 'my_image.heic', { type: 'image/heic' })
    const result = await resizeImage(file)
    expect(result.name).toBe('my_image.jpg')
  })

  it('calls URL.revokeObjectURL after processing', async () => {
    const file = new File(['x'], 'a.jpg', { type: 'image/jpeg' })
    await resizeImage(file)
    expect(URL.revokeObjectURL).toHaveBeenCalledWith('blob:fake')
  })
})
