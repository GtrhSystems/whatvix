<script setup>
import UserLayout from '@/Layouts/User/UserLayout.vue'

import Preview from '../../Components/Preview.vue'
import InteractivePreview from '../../Components/Preview/InteractiveMessage.vue'

defineOptions({ layout: UserLayout })
const props = defineProps({
  template: {
    type: Object,
    default: {}
  }
})
</script>
<template>
  <div class="mx-auto max-w-2xl">
    <div class="card p-4">
      <p class="mb-2 ml-1 text-lg">{{ trans('Preview') }}</p>
      <InteractivePreview v-if="template.type == 'interactive'" :components="template.meta || []" />
      <Preview v-else :templateData="template.meta?.components || []" />
    </div>
  </div>
</template>
