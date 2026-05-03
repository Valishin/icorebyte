import '@inertiajs/vue3'

declare module '@inertiajs/core' {
  interface PageProps {
    flash: {
      success?: string
      error?: string
    }
  }
}
