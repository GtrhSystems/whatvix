<script setup>
import { useChatStore } from '@whatsappWeb/Stores/chatStore'

const chatStore = useChatStore()
</script>

<template>
  <div
    class="absolute -top-full min-w-[40rem] max-w-[90%] rounded border bg-gray-50 p-2 opacity-80 dark:bg-dark-900"
  >
    <div class="flex items-center justify-between">
      <div>
        <i class="text-sm text-gray-500">{{ trans('Replying...') }}</i>
        <p class="text-sm font-semibold">
          {{
            chatStore.replying?.message?.conversation ??
            chatStore.replying?.message?.extendedTextMessage?.text
          }}
        </p>
      </div>
      <button
        @click="chatStore.unsetReplying"
        class="rounded-lg border bg-white p-2 py-1 hover:bg-slate-400 dark:bg-dark-700"
      >
        X
      </button>
    </div>
  </div>
</template>
