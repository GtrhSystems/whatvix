<script setup>
import { useChatStore } from '@/Store/chatStore'

const chatStore = useChatStore()
</script>

<template>
  <div class="absolute -top-24 left-1 rounded-lg bg-white p-2 drop-shadow dark:bg-dark-900">
    <div v-for="(item, index) of chatStore.quickReplySuggestionItems" :key="index">
      <div
        class="my-1 cursor-pointer rounded px-1.5 py-1 text-sm shadow-sm hover:bg-primary-100 dark:bg-dark-700 dark:hover:bg-opacity-80"
        :class="{
          'bg-primary-200 dark:bg-primary-500': chatStore.arrowCounter == index,
        }" @click="chatStore.addQuickReplyToMessageInput(item)">
        {{ item }}
      </div>
    </div>
  </div>
</template>
