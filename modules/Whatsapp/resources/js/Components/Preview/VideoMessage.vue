<script setup>
import { computed } from 'vue'
const { message } = defineProps(['message'])
const url = computed(() => message.meta?.media_url ?? message.body?.link ?? false)
const caption = computed(() => message.body?.caption ?? false)
</script>

<template>
    <video v-if="url" :src="url" controls />
    <p v-if="caption" class="dark:bg-dark-800 px-2 py-1 rounded">{{ caption }}</p>
</template>