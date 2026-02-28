<script setup>
import { computed } from 'vue'

const props = defineProps(['meta', 'text'])

const previewMessage = computed(
  () => props.meta.text || props.text || 'Your message will appear here'
)
</script>

<template>
  <div>
    <p class="rounded-lg bg-gray-100 p-2 text-[11px] leading-4 dark:bg-dark-800">
      {{ previewMessage }}
    </p>
    <p class="text-end text-[10px] text-gray-400">{{ new Date().toLocaleTimeString() }}</p>
  </div>
</template>
