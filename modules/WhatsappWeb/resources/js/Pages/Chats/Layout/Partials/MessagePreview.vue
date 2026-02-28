<script setup>
import { ref, onMounted } from 'vue'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import { computed } from 'vue'
import PollPreview from '@whatsappWeb/Pages/Templates/Partials/PollPreview.vue'
import LocationPreview from '@whatsappWeb/Pages/Templates/Partials/LocationPreview.vue'

const chatStore = useChatStore()

// Define props
const props = defineProps({
  message: {
    type: Object,
    required: true
  }
})

// Reactive variable to store the image URL
const mediaUrl = ref(null)
const mediaLoaded = ref(false)

onMounted(() => {
  let messageType = Object.keys(props.message.message ?? [])[0]

  let downloadable = [
    'imageMessage',
    'videoMessage',
    'audioMessage',
    'documentMessage',
    'stickerMessage'
  ]
  if (downloadable.includes(messageType)) {
    chatStore
      .getMedia(props.message)
      .then((url) => {
        mediaUrl.value = url
      })
      .finally(() => {
        mediaLoaded.value = true
      })
  }
  setTimeout(() => {
    mediaLoaded.value = true
  }, 1000 * 30)
})

const messageTypeIs = (type) => {
  let messageType = null

  let message = props.message.message ?? {}

  if (
    Object.keys(message).includes('conversation') ||
    Object.keys(message).includes('extendedTextMessage')
  ) {
    messageType = 'text'
  } else if (Object.keys(message).includes('imageMessage')) {
    messageType = 'image'
  } else if (Object.keys(message).includes('audioMessage')) {
    messageType = 'audio'
  } else if (Object.keys(message).includes('videoMessage')) {
    messageType = 'video'
  } else if (Object.keys(message).includes('documentMessage')) {
    messageType = 'document'
  } else if (Object.keys(message).includes('stickerMessage')) {
    messageType = 'sticker'
  } else if (Object.keys(message).includes('pollCreationMessage')) {
    messageType = 'poll'
  } else if (Object.keys(message).includes('locationMessage')) {
    messageType = 'location'
  } else if (Object.keys(message).includes('reactionMessage')) {
    messageType = 'reaction'
  } else if (Object.keys(message).includes('editedMessage')) {
    messageType = 'edited'
  } else if (Object.keys(message).includes('protocolMessage')) {
    if (message.protocolMessage.type == 'REVOKE') {
      messageType = 'deleted'
    }
  }

  return messageType === type
}

const getReplyMessage = computed(() => {
  return props.message.message?.extendedTextMessage?.contextInfo?.quotedMessage?.conversation
})

const messageDirectionIs = (type) => {
  let messageType = props.message.key?.fromMe ? 'out' : 'in'
  return messageType === type
}
</script>

<template>
  <div
    :title="message.type"
    class="text-danger relative rounded-primary rounded-tl-none bg-slate-100 p-2 text-sm group-[.pr]:rounded-tl-primary group-[.pr]:rounded-tr-none group-[.pr]:text-white dark:bg-slate-700 dark:text-slate-300"
    :class="{ 'group-[.pr]:bg-primary-500': messageTypeIs('text') || messageTypeIs('edited') }"
  >
    <div>
      <span
        class="absolute -bottom-3 right-2 rounded-full bg-white/50 p-1 text-sm"
        v-if="message.reactions?.[0]?.text"
      >
        {{ message.reactions[0].text }}
      </span>

      <div class="rounded bg-slate-200 p-2 italic dark:bg-gray-800" v-if="getReplyMessage">
        {{ getReplyMessage }}
      </div>

      <!-- Text message -->
      <p v-if="messageTypeIs('text')">
        {{ message.message.conversation ?? message.message.extendedTextMessage?.text }}
      </p>

      <!-- Text message -->
      <p v-else-if="messageTypeIs('deleted')" class="italic text-gray-400">
        {{ trans('This message was deleted') }}
      </p>

      <!-- Image or sticker message -->
      <div v-else-if="messageTypeIs('image') || messageTypeIs('sticker')">
        <p v-if="!mediaLoaded">{{ trans('Loading media...') }}</p>
        <template v-else>
          <p class="pb-2 text-black dark:text-white" v-if="message.message.imageMessage?.caption">
            {{ message.message.imageMessage?.caption }}
          </p>
          <a v-if="mediaUrl" :href="mediaUrl">
            <img :src="mediaUrl" class="max-h-60 rounded" />
          </a>
          <p class="italic" v-else>{{ trans('Failed to load media') }}</p>
        </template>
      </div>

      <!-- Audio message -->
      <div v-else-if="messageTypeIs('audio')">
        <p class="pb-2" v-if="message.message.audioMessage?.caption">
          {{ message.message.audioMessage?.caption }}
        </p>
        <audio v-if="mediaUrl" :src="mediaUrl" controls></audio>
        <p v-else-if="!mediaLoaded">{{ trans('Audio not available') }}</p>
      </div>

      <!-- Video message -->
      <div v-else-if="messageTypeIs('video')">
        <p class="pb-2" v-if="message.message.videoMessage?.caption">
          {{ message.message.videoMessage?.caption }}
        </p>
        <video v-if="mediaUrl" :src="mediaUrl" controls></video>
        <p v-else>{{ trans('Video not available') }}</p>
      </div>

      <!-- Document message -->
      <div v-else-if="messageTypeIs('document')">
        <p class="pb-2" v-if="message.message.documentMessage?.caption">
          {{ message.message.documentMessage?.caption }}
        </p>
        <a target="_blank" :href="mediaUrl" class="flex items-center gap-3 rounded border p-3">
          <Icon icon="bx:file" />
          <p>{{ message.message.documentMessage?.fileName }}</p>
        </a>
      </div>

      <!-- Poll message -->
      <div v-else-if="messageTypeIs('poll')">
        <PollPreview :meta="message.message.pollCreationMessage" />
      </div>

      <!-- Location message -->
      <div v-else-if="messageTypeIs('location')">
        <LocationPreview :meta="message.message.locationMessage" />
      </div>

      <!-- Edited message -->
      <div v-else-if="messageTypeIs('edited')">
        <p>
          {{ message.message.editedMessage.message.protocolMessage.editedMessage.conversation }}
        </p>
        <small>Edited</small>
      </div>

      <!-- Unsupported message -->
      <div v-else>
        <p class="text-black dark:text-white">{{ trans('Unsupported message type') }}</p>
      </div>
    </div>
  </div>

  <button
    v-if="!messageTypeIs('deleted') && !messageDirectionIs('out')"
    @click="chatStore.setReplying(message)"
    class="rounded-full bg-gray-200 p-1 text-xl text-black hover:bg-gray-500"
  >
    <Icon icon="bx:reply" />
  </button>
</template>
