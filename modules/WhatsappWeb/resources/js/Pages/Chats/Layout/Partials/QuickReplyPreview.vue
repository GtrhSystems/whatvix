<script setup>
import { useChatStore } from '@whatsappWeb/Stores/chatStore'

const chatStore = useChatStore()
</script>

<template>
  <div class="absolute -top-[108px] left-1 rounded-lg bg-white p-2 drop-shadow dark:bg-dark-900">
    <div v-for="(item, index) of chatStore.quickReplySuggestionItems" :key="index">
      <div
        class="my-1 cursor-pointer rounded border px-1 py-2 text-sm shadow-sm hover:border-primary-300 hover:bg-primary-100 dark:bg-dark-700 dark:hover:bg-opacity-80"
        :class="{
          'border border-primary-300 bg-primary-50': chatStore.arrowCounter == index
        }"
        @click="chatStore.addQuickReplyToMessageInput(item)"
      >
        {{ item }}
      </div>
    </div>
  </div>
</template>
