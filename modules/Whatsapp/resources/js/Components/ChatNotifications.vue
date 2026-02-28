<script setup>
import { computed, onMounted, ref } from 'vue'

import axios from 'axios'

import Dropdown from '../../../../../public/assets/js/js/Components/dropdown'

const props = defineProps(['authId', 'audio'])

onMounted(() => {
  Dropdown.init()
  setTimeout(() => {
    getMessages()
    setupChatNotifications()
  }, 500)
})

const setupChatNotifications = () => {
  window.Echo.private(`Chat.User.${props.authId}`).listen(
    '.newMessageReceived',
    function (newMessage) {
      messages.value.push(newMessage)
      console.log('form notification', newMessage)
      ToastAlert('success', 'You have a new message')
      const audio = new Audio(props.audio)
      if (audio) {
        audio.play()
      }
    }
  )
}

const messages = ref([])

const getMessages = () => {
  axios
    .get('/whatsapp/user/chat-notification')
    .then((response) => {
      messages.value = response.data
    })
    .catch((error) => {
      console.log(error)
    })
}

const totalMessageCount = computed(() => messages.value.length)

const textLimit = (text) => {
  return text.length > 20 ? text.substring(0, 20) + '...' : text
}
const messageShow = (message) => {
  return message.type == 'text' ? textLimit(message.body) : message.type
}
</script>

<template>
  <!-- Notification Dropdown Starts -->
  <div class="dropdown -mt-0.5" data-strategy="absolute">
    <div class="dropdown-toggle px-3">
      <button
        class="relative mt-1 flex items-center justify-center rounded-full text-slate-500 transition-colors duration-150 hover:text-slate-700 focus:text-primary-500 dark:text-slate-400 dark:hover:text-slate-300 dark:focus:text-primary-500"
      >
        <i class="bx bx-message-detail text-2xl"></i>
        <span
          v-if="totalMessageCount"
          class="absolute -right-1 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-danger-500 text-[11px] text-slate-200"
        >
          {{ totalMessageCount }}
        </span>
      </button>
    </div>

    <div class="dropdown-content mt-3 w-[17.5rem] divide-y dark:divide-slate-700 sm:w-80">
      <div class="flex items-center justify-between px-4 py-4">
        <h6 class="text-slate-800 dark:text-slate-300">{{ trans('Chat Messages') }}</h6>
      
      </div>

      <div class="h-80 w-full" data-simplebar>
        <ul>
          <li
            v-for="(chatMessage, index) in messages"
            :key="index"
            class="flex cursor-pointer gap-4 px-4 py-3 transition-colors duration-150 hover:bg-slate-100/70 dark:hover:bg-slate-700"
          >
            <div
              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-600 text-white"
            >
              <i class="bx bxl-whatsapp text-2xl"></i>
            </div>

            <div>
              <a :href="'/whatsapp/user/chat-messages/' + chatMessage.chat_id">
                <h6 class="text-sm font-normal">
                  {{ chatMessage.chat?.device?.name }}
                </h6>
                <p class="text-xs text-slate-400">
                  {{ messageShow(chatMessage) }}
                </p>
                <p class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                  <i data-feather="clock" width="1em" height="1em"></i>
                  <span>{{ chatMessage.created_at_diff }}</span>
                </p>
              </a>
            </div>
          </li>
        </ul>
      </div>

      <div class="px-4 py-2">
        <a href="/whatsapp/user/chat-devices" class="btn btn-primary-plain btn-sm w-full">
          <span>{{ trans('View More') }}</span>
          <i class="bx bx-arrow-right text-2xl"></i>
        </a>
      </div>
    </div>
  </div>
  <!-- Notification Dropdown Ends -->
</template>
