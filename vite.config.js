'use strict'

import laravel from 'laravel-vite-plugin'
import path from 'path'
import DefineOptions from 'unplugin-vue-define-options/vite'
import { defineConfig } from 'vite'
import svgLoader from 'vite-svg-loader'

import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'resources/css/app.css', 'resources/scss/admin/main.scss'],
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
    DefineOptions(),
    svgLoader()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@modules': path.resolve(__dirname, 'modules'),

      // modules
      '@NumberChecker': path.resolve(__dirname, 'modules/NumberChecker/resources/js/'),
      '@QAReply': path.resolve(__dirname, 'modules/QAReply/resources/js/'),
      '@WebScraping': path.resolve(__dirname, 'modules/WebScraping/resources/js/'),
      '@whatsapp': path.resolve(__dirname, 'modules/Whatsapp/resources/js/'),
      '@Flow': path.resolve(__dirname, 'modules/Flow/resources/js/'),
      '@whatsappWeb': path.resolve(__dirname, 'modules/WhatsappWeb/resources/js/')
    }
  }
})
