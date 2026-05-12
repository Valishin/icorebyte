/** t ∈ [0,1] → ease out cuartic */
export const easeOutQuart = (t: number): number => 1 - Math.pow(1 - t, 4)

/** Interpolación lineal */
export const lerp = (a: number, b: number, t: number): number => a + (b - a) * t

/** Clamp numérico */
export const clamp = (v: number, lo: number, hi: number): number =>
  Math.min(hi, Math.max(lo, v))
