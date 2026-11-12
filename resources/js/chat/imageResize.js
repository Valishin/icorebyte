const MAX_SIDE    = 2000
const JPEG_QUALITY = 0.82

/**
 * Resize an image File to MAX_SIDE px on its longest side, re-encode as JPEG.
 * Strips EXIF/GPS by drawing through <canvas>.
 * Returns a Promise<File>.
 */
export function resizeImage(file) {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file)
    const img = new Image()

    img.onload = () => {
      URL.revokeObjectURL(url)

      let { naturalWidth: w, naturalHeight: h } = img

      if (w <= MAX_SIDE && h <= MAX_SIDE) {
        // Already within limits — still re-encode to strip EXIF
        const canvas  = document.createElement('canvas')
        canvas.width  = w
        canvas.height = h
        canvas.getContext('2d').drawImage(img, 0, 0)
        canvas.toBlob(
          (blob) => resolve(new File([blob], toJpgName(file.name), { type: 'image/jpeg' })),
          'image/jpeg',
          JPEG_QUALITY,
        )
        return
      }

      const ratio = Math.min(MAX_SIDE / w, MAX_SIDE / h)
      w = Math.round(w * ratio)
      h = Math.round(h * ratio)

      const canvas  = document.createElement('canvas')
      canvas.width  = w
      canvas.height = h
      canvas.getContext('2d').drawImage(img, 0, 0, w, h)
      canvas.toBlob(
        (blob) => {
          if (!blob) { reject(new Error('toBlob failed')); return }
          resolve(new File([blob], toJpgName(file.name), { type: 'image/jpeg' }))
        },
        'image/jpeg',
        JPEG_QUALITY,
      )
    }

    img.onerror = () => {
      URL.revokeObjectURL(url)
      reject(new Error('Failed to load image'))
    }

    img.src = url
  })
}

function toJpgName(name) {
  return name.replace(/\.[^.]+$/, '') + '.jpg'
}
