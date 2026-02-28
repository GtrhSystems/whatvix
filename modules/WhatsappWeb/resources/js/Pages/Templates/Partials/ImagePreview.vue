<script setup>
import { computed } from 'vue'

const props = defineProps(['meta'])

const previewCaption = computed(() => props.meta.caption || 'Your caption will appear here')
const imageUrl = computed(
  () => props.meta?.imageUrl || props.meta?.image || '/assets/images/whatsapp-demo-img.png'
)
</script>

<template>
  <div class="rounded-lg bg-white p-1 dark:bg-dark-700">
    <img class="rounded-lg" :src="imageUrl" alt="Preview image" />
    <p class="-mb-1 mt-1 p-1 text-sm leading-3">{{ previewCaption }}</p>
    <p class="text-end text-[10px] text-gray-400">{{ new Date().toLocaleTimeString() }}</p>
  </div>
</template>
