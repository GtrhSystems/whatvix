<script setup>
const { message } = defineProps(['message'])
const url = message.meta?.media_url ?? message.body?.link ?? false
</script>

<template>
  <a
    target="_blank"
    :href="url"
    class="flex items-center gap-2 rounded border bg-slate-200 px-3 py-2 text-gray-900 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-100"
  >
    <Icon icon="bx:file" />
    <p>{{ message.meta?.filename || message.body?.caption || trans('Document') }}</p>
  </a>
</template>
