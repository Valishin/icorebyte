import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import { defineConfig } from 'vite'
import svgLoader from 'vite-svg-loader'

export default defineConfig({
  plugins: [
    laravel({
      input: 'resources/js/app.js',
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    svgLoader()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@assets': path.resolve(__dirname, 'resources/assets'),
      '@scss': path.resolve(__dirname, 'resources/scss')
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `
        @use "@scss/tokens/colors" as *;
        @use "@scss/tokens/spacing" as *;
        @use "@scss/tokens/grid" as *;
        @use "@scss/tokens/breakpoints" as *;
        @use "@scss/tokens/tipography" as *;
        @use "@scss/mixins/mixins" as *;
      `
      }
    }
  },
  server: {
    host: '0.0.0.0',
    cors: true,
    hmr: {
      host: '192.168.1.54',
      port: 5173
    }
  }
})
