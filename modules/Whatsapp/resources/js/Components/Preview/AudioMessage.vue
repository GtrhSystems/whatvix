<script setup>
import { computed } from 'vue'
const { message } = defineProps(['message'])
const url = computed(() => message.meta?.media_url ?? message.body?.link ?? false)
</script>

<template>
    <audio v-if="url" controls :src="url" />
</template>