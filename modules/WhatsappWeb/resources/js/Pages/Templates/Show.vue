<script setup>
import UserLayout from '@/Layouts/User/UserLayout.vue'
import TemplatePreview from '@whatsappWeb/Pages/Templates/Partials/TemplatePreview.vue'

defineOptions({ layout: UserLayout })
const props = defineProps({
  template: {
    type: Object,
    default: {}
  }
})
</script>
<template>
  <div class="mx-auto max-w-sm">
    <div class="card p-4">
      <p class="mb-2 ml-1 text-lg">{{ trans('Preview') }}</p>
      <div class="mx-auto w-full max-w-xs sm:col-span-3" v-if="template">
        <div
          class="whatsapp-chat-body relative h-[35rem] rounded-xl border-2 border-dark-400 outline outline-4 outline-dark-500 dark:border-dark-800 dark:outline-dark-950"
        >
          <div
            class="absolute bottom-3 left-4 w-10/12 rounded-lg bg-white p-1 dark:bg-dark-700 xl:w-8/12"
          >
            <TemplatePreview :template="template" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
