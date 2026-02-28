<script setup>
import { onMounted, ref } from 'vue'

import moment from 'moment'
import { storeToRefs } from 'pinia'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import IntersectionObserver from '@/Components/IntersectionObserver.vue'
import toast from '@/Composables/toastComposable'
import { useAssetStore } from '@/Store/assetStore'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import { useModalStore } from '@/Store/modalStore'

import Header from './Header.vue'
import VoiceRecorder from '@/Components/Chats/VoiceRecorder.vue'
import MediaPreview from './Partials/MediaPreview.vue'
import QuickReplyPreview from './Partials/QuickReplyPreview.vue'
import ReplyPreview from './Partials/ReplyPreview.vue'
import MessagePreview from './Partials/MessagePreview.vue'

const assetStore = useAssetStore()
const chatStore = useChatStore()
const modalStore = useModalStore()

const {
  loading,
  assetPopup,
  inputMessage,
  activeConversation,
  messageInputFieldRef,
  quickReplySuggestionItems
} = storeToRefs(chatStore)

onMounted(() => {
  getAiTemplates()
  setTimeout(() => {
    messageInputFieldRef.value?.focus()
  }, 500)
})
const getAiTemplates = () => {
  axios
    .get(route('user.conversations.api', { data: 'ai_templates' }))
    .then((res) => {
      chatStore.$patch({ aiTemplates: res.data.data })
    })
    .catch((err) => console.error(err))
}
const loadMoreArea = ref()

const attachMedia = (mediaType) => {
  assetStore.openModal({
    load: mediaType,
    multiple: true,
    caption: ['image', 'video'].includes(mediaType) ? '' : undefined,
    button: {
      text: 'Send'
    },
    onOpen: () => {
      assetPopup.value = false
      inputMessage.value.type = mediaType
    },
    onSelect: (asset) => {},
    onSubmit: (assets, state) => {
      let paths = assets.map((asset) => asset.path)
      inputMessage.value.file = paths[0] ?? false
      inputMessage.value.caption = state.caption

      if (!inputMessage.value.file) {
        return toast.warning('Attachment not found, please try again')
      }

      chatStore.submitMessage()
    }
  })
}

const sendMessage = () => {
  inputMessage.value.type = 'text'
  if (!inputMessage.value.message) {
    return toast.warning('Please enter a message')
  }
  chatStore.submitMessage()
}
</script>

<template>
  <div
    class="relative ml-2 flex h-[calc(100vh-11rem)] w-full flex-col overflow-hidden rounded-md bg-white shadow transition-all dark:bg-dark-800"
  >
    <Header />
    <div class="styled-scrollbar whatsapp-chat-body relative h-[82%] overflow-auto px-4 pb-8">
      <ul class="space-y-3" id="scrollContainerRef" v-if="activeConversation?.messages?.length">
        <li class="my-2 text-center">
          <IntersectionObserver
            :afterIntersection="chatStore.loadMoreMessages"
            :loader="loading.messages"
          />
        </li>

        <li
          v-for="(message, index) in activeConversation?.messages ?? []"
          class="group"
          :key="index"
          :class="{ pr: message?.key?.fromMe }"
        >
          <div
            v-if="!message?.message?.reactionMessage"
            class="flex gap-x-3 group-[.pr]:flex-row-reverse"
          >
            <div v-if="!message?.key?.fromMe" class="avatar avatar-circle avatar-sm shrink-0">
              <img
                :src="chatStore.getConversationProfilePic(activeConversation)"
                class="avatar-img"
                alt="profile-img"
              />
            </div>
            <div class="flex max-w-sm flex-col items-start group-[.pr]:items-end">
              <p
                v-if="message.meta?.context?.id"
                class="rounded-t-lg bg-white bg-opacity-50 p-2 text-sm italic text-gray-500"
              >
                {{ message.meta?.context?.title }}
              </p>

              <div class="flex items-center">
                <MessagePreview :message="message" />
              </div>

              <div class="mb-2">
                <span
                  class="text-xs font-normal text-slate-400"
                  :title="
                    moment.unix(message.messageTimestamp).local().format('D MMM, YYYY h:mm A')
                  "
                >
                  {{ moment.unix(message.messageTimestamp).local().format('D MMM, h:mm A') }}
                </span>
                <span
                  v-if="message?.key?.fromMe"
                  class="font-bolder ml-1 text-slate-300"
                  :title="
                    message.status == 1
                      ? 'Pending'
                      : message.status == 2
                      ? 'Sent'
                      : message.status == 3
                      ? 'Delivered'
                      : message.status == 4
                      ? 'Seen'
                      : ''
                  "
                >
                  <i v-if="message.status == 1" class="bx bx-circle"></i>
                  <i v-else-if="message.status == 2" class="bx bx-check"></i>
                  <i v-else-if="message.status == 3" class="bx bx-check-double"></i>
                  <i v-else-if="message.status == 4" class="bx bx-check-double text-green-600"></i>
                </span>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <div v-else class="h-200 flex items-center justify-center">
        <p class="text-center italic text-slate-600">No messages</p>
      </div>
    </div>

    <div
      class="relative bottom-0 left-0 z-10 rounded-b-primary border-t bg-white dark:border-dark-700 dark:bg-dark-800"
    >
      <ReplyPreview v-if="chatStore.isReplying" />
      <MediaPreview />
      <VoiceRecorder v-if="inputMessage.type === 'voice'" :store="chatStore" />
      <QuickReplyPreview v-show="quickReplySuggestionItems.length > 0" />

      <form
        v-if="inputMessage.type != 'voice'"
        @submit.prevent="sendMessage"
        class="flex h-[4.2rem] items-center rounded-primary shadow"
      >
        <textarea
          ref="messageInputFieldRef"
          v-model="inputMessage.message"
          @keyup.down="onArrowDown"
          @keyup.up="onArrowUp"
          @keydown.tab.prevent="selectQuickReply"
          @keydown.enter.exact.prevent="sendMessage"
          :disabled="loading.sendingMessage"
          class="max-h-16 min-h-16 w-full border-transparent bg-transparent px-4 text-sm text-slate-700 placeholder:text-slate-500 focus:border-transparent focus:ring-0 dark:text-slate-300 dark:placeholder:text-slate-400"
          placeholder="Type message... enter to send, shift+enter to next line"
        ></textarea>

        <div class="flex items-center gap-x-4 px-4">
          <button
            type="button"
            @click="modalStore.open('aiModal')"
            class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
          >
            <Icon icon="fluent:chat-sparkle-16-regular" />
          </button>

          <button
            type="button"
            @click="chatStore.quickReplyModalOpen"
            class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
          >
            <Icon icon="bxs:layer" />
          </button>

          <button
            type="button"
            @click="modalStore.open('TemplateMessageModal')"
            class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
          >
            <Icon icon="bxs:grid" />
          </button>

          <button
            v-if="chatStore.features?.voice_message"
            type="button"
            @click="inputMessage.type = 'voice'"
            class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
          >
            <Icon icon="bxs:microphone" />
          </button>
          <div class="relative">
            <button
              id="btn-upload-media"
              @click="assetPopup = !assetPopup"
              type="button"
              class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
            >
              <Icon icon="bx:link-alt" />
            </button>
            <div
              class="fixed inset-0 bg-transparent"
              v-if="assetPopup"
              @click="assetPopup = false"
            ></div>
            <transition name="fade">
              <div v-if="assetPopup" class="absolute bottom-full right-0">
                <button
                  type="button"
                  class="absolute -right-2 -top-3 rounded-full border border-red-500 bg-white p-1 text-end text-black hover:bg-gray-100 dark:bg-dark-900 dark:text-slate-300 dark:hover:bg-dark-700"
                  @click="assetPopup = false"
                >
                  <Icon icon="bx:x" class="size-3 text-red-500" />
                </button>
                <div class="mb-2 space-y-2 rounded-md bg-dark-900 p-3">
                  <button
                    @click="() => attachMedia('document')"
                    type="button"
                    class="btn btn-dark btn-xs w-full"
                  >
                    <Icon icon="bx:file"></Icon>
                    <span>{{ trans('Documents') }}</span>
                  </button>

                  <button
                    @click="attachMedia('image')"
                    type="button"
                    class="btn btn-dark btn-xs w-full"
                  >
                    <Icon icon="bx:image"></Icon>
                    <span>{{ trans('Image') }}</span>
                  </button>

                  <button
                    @click="() => attachMedia('video')"
                    type="button"
                    class="btn btn-dark btn-xs w-full"
                  >
                    <Icon icon="bx:video"></Icon>
                    <span>{{ trans('Video') }}</span>
                  </button>

                  <button
                    @click="() => attachMedia('audio')"
                    type="button"
                    class="btn btn-dark btn-xs w-full"
                  >
                    <Icon icon="bx:microphone"></Icon>
                    <span>{{ trans('Audio') }}</span>
                  </button>
                  <button
                    @click="() => modalStore.open('locationMessageModal')"
                    type="button"
                    class="btn btn-dark btn-xs w-full"
                  >
                    <Icon icon="bx:map"></Icon>
                    <span> {{ trans('Location') }}</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
          <SpinnerBtn icon="bx:send" btn-text="" :disabled="loading.sendingMessage" />
        </div>
      </form>
    </div>
  </div>
</template>
