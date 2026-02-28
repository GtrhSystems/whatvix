import './bootstrap'
import '@vueform/multiselect/themes/default.css'

import { createApp, h } from 'vue'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import VueLazyLoad from 'vue3-lazyload'

import { Icon } from '@iconify/vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable.js'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'
import Modal from '@/Components/Dashboard/Modal.vue'


const { sanitizeUrl, sanitizeHtml } = sharedComposable()
const appName = document.querySelector('meta[name="app-name"]')?.content || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .mixin({
        methods: {
          trans,
          route: window.route,
          sanitizeUrl,
          sanitizeHtml
        }
      })
      .component('Link', Link)
      .component('Icon', Icon)
      .component('Modal', Modal)
      .component('PageHeader', PageHeader)
      .use(plugin)
      .use(VueLazyLoad)
      .use(createPinia())
      .mount(el)
  },
  progress: {
    color: '#d2f34c'
  }
})
