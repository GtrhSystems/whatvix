<script setup>
const { message } = defineProps(['message'])
const url = message.meta?.media_url ?? message.body?.link ?? false
const caption = message.body?.caption ?? false
</script>

<template>
    <img class="w-32" v-if="url" :src="url" />
    <p class="text-gray-400" v-else>{{ trans('Sticker Not Available') }}</p>
    <p class="caption" v-if="caption">{{ caption }}</p>
</template>