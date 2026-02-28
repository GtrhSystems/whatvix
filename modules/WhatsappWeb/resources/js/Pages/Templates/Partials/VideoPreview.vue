<script setup>
import { computed } from 'vue'

const props = defineProps(['meta'])

const previewCaption = computed(() => props.meta.caption || 'Your caption will appear here')
const videoUrl = computed(() => props.meta?.videoUrl || props.meta?.video)
</script>

<template>
  <div class="rounded-lg bg-white p-1 dark:bg-dark-700">
    <video v-if="videoUrl" class="rounded-lg" :src="videoUrl" controls></video>
    <img
      v-else
      class="rounded-lg"
      src="/assets/images/whatsapp-demo-video.png"
      alt="Default video thumbnail"
    />
    <p class="-mb-1 mt-1 p-1 text-sm leading-3">{{ previewCaption }}</p>
    <p class="text-end text-[10px] text-gray-400">{{ new Date().toLocaleTimeString() }}</p>
  </div>
</template>
