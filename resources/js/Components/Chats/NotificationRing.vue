<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import echoServiceComposable from '@/Composables/echoServiceComposable'

const ownerId = usePage().props.authUser?.active_workspace?.owner_id ?? null

const props = defineProps({
  module: {
    type: String,
    default: 'whatsapp'
  }
})

const echoService = echoServiceComposable.connect()

const messages = ref([])
const unreadMessageCounter = computed(() => messages.value.length)

onMounted(() => {
  if (!ownerId) {
    return console.log('Owner id not found')
  }

  echoService
    ?.private(`live-chat.${props.module}.${ownerId}`)
    .subscribed(() => console.log('Live chat counter activated successfully'))
    .listen('LiveChatNotifyEvent', (payload) => {
      console.log(`New event received for: ${props.module}`, payload)

      if (props.module == 'whatsapp') {
        pushMessage({
          platform_id: payload.platform_id,
          conversation_id: payload.conversation_id,
          message_id: payload.message_id
        })
      }

      if (props.module == 'whatsapp-web') {
        if (payload.event == 'messages.upsert') {
          pushMessage({ sessionId: payload.sessionId })
        }
      }
    })
    .error((err) => console.error(err))
})

const pushMessage = (message) => {
  messages.value.push(message)
  playSound()
}

onUnmounted(() => {
  echoService?.leave(`live-chat.${props.module}.${ownerId}`)
  console.log('Live chat counter left successfully')
})

const playSound = () => {
  let toneLocation = '/assets/incoming-message-online-whatsapp.mp3'
  let audio = new Audio(toneLocation)
  audio.play().catch((e) => console.warn('Audio playback error:', e))
}

const lastMessage = computed(() => {
  return messages.value[messages.value.length - 1]
})

const getRedirectUrl = computed(() => {
  if (props.module === 'whatsapp') {
    return `/user/${props.module}/platforms/${lastMessage.value.platform_id}/conversations/${lastMessage.value.conversation_id}`
  }

  if (props.module === 'whatsapp-web') {
    return `/user/whatsapp-web/platforms/${lastMessage.value.sessionId}`
  }
})
</script>

<template>
  <div v-if="unreadMessageCounter > 0" class="fixed bottom-12 right-12">
    <div class="relative flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-700">
      <Link :href="getRedirectUrl">
        <img src="/assets/svg/alert-counter.gif" alt="" />
      </Link>
      <span
        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-normal text-white"
      >
        {{ unreadMessageCounter }}
      </span>
    </div>
  </div>
</template>
