<script setup>
import { useChatStore } from '@/Store/chatStore'

const chatStore = useChatStore()
</script>

<template>
  <div
    class="absolute -top-full mb-1 ms-1 w-4/5 rounded border bg-gray-50 p-2 dark:border-gray-500 dark:bg-dark-900"
  >
    <div class="flex items-center justify-between">
      <div>
        <i class="text-sm text-gray-500">{{ trans('Replying') }}...</i>
        <p class="text-sm font-semibold">{{ chatStore.replying.message }}</p>
      </div>
      <button
        @click="chatStore.unsetReplying"
        class="rounded-lg border bg-white p-2 py-1 hover:bg-slate-400 dark:border-gray-500 dark:bg-dark-800 dark:hover:bg-dark-600"
      >
        X
      </button>
    </div>
  </div>
</template>
