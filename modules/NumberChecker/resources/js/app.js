"use strict";

import '@/bootstrap'
import '@vueform/multiselect/themes/default.css'

import { createApp, h } from 'vue'

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import VueLazyLoad from 'vue3-lazyload'

import { Icon } from '@iconify/vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
import trans from '@/Composables/transComposable'

const appName = document.querySelector('meta[name="app-name"]')?.content || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .mixin({ methods: { trans, route: window.route } })
      .component('Link', Link)
      .component('Icon', Icon)
      .use(plugin)
      .use(VueLazyLoad)
      .use(createPinia())
      .mount(el)
  },
  progress: {
    color: '#d2f34c',
    showSpinner: true
  }
})
