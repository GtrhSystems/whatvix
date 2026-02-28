<script setup>
import { computed } from 'vue'
const { message } = defineProps(['message'])
const link = computed(() => message.body?.link ?? message.meta?.media_url ?? false)
const caption = computed(() => message.body?.caption ?? false)
</script>

<template>
  <img v-if="link" :src="link" />
  <p v-if="caption" class="text-black dark:text-white">{{ caption }}</p>
</template>
