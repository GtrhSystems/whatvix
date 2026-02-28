<script setup>
import { storeToRefs } from 'pinia'

import { useChatStore } from '@whatsappWeb/Stores/chatStore'

const { inputMessage } = storeToRefs(useChatStore())
</script>

<template>
  <div v-if="inputMessage.file" class="absolute -top-[140px] right-2 z-40">
    <div class="flex gap-2">
      <img
        v-if="['image/jpeg', 'image/jpg', 'image/png'].includes(inputMessage.type)"
        class="h-32 w-32 rounded-md border shadow-md"
        :src="inputMessage.url"
        alt="media-img"
      />
      <a
        target="_blank"
        v-else-if="['application/pdf', 'text/csv', 'application/xls'].includes(inputMessage.type)"
        class="flex items-center gap-3 rounded border p-3"
        :href="inputMessage.url"
      >
        <i class="fa-solid fa-file-pdf"></i>
        <p>{{ inputMessage.file_caption }}</p>
      </a>
      <button type="button" @click="removeFile">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
  </div>
</template>
