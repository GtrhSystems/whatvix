import laravel from 'laravel-vite-plugin'
import path from 'path'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const moduleName = path.basename(__dirname)

export default defineConfig({
  build: {
    outDir: 'public/build-modules/' + moduleName,
    emptyOutDir: true,
    manifest: 'manifest.json'
  },
  plugins: [
    laravel({
      publicDirectory: '../../public',
      buildDirectory: 'build-modules/' + moduleName,
      input: [
        __dirname + '/resources/js/app.js',
        'resources/css/app.css',
        'resources/scss/admin/main.scss'
      ],
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    })
  ],
  resolve: {
    alias: {
      '@modules': path.resolve(__dirname, '../'),
      '@root': path.resolve(__dirname, './../../resources/js/'),

      '@whatsapp': path.resolve(__dirname, './resources/js/'),
      '@messenger': path.resolve(__dirname, '../Messenger/resources/js/'),
      '@telegram': path.resolve(__dirname, '../Telegram/resources/js/'),
      '@instagram': path.resolve(__dirname, '../Instagram/resources/js/')
    }
  }
})