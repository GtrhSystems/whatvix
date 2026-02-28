<script setup>
import { defineAsyncComponent, onMounted, ref } from 'vue'

import moment from 'moment'
import { storeToRefs } from 'pinia'

import DotLoader from '@/Components/DotLoader.vue'
import IntersectionObserver from '@/Components/IntersectionObserver.vue'
import toast from '@/Composables/toastComposable'
import { useAssetStore } from '@/Store/assetStore'
import { useChatStore } from '@/Store/chatStore'
import { useModalStore } from '@/Store/modalStore'

import Header from './Header.vue'
import CustomTextarea from './CustomTextarea.vue'
import VoiceRecorder from './VoiceRecorder.vue'
import QuickReplyPreview from './QuickReplyPreview.vue'
import ReplyPreview from './ReplyPreview.vue'
import LoadingMessagePreview from './LoadingMessagePreview.vue'
import ErrorMessagePreview from './ErrorMessagePreview.vue'

const MessagePreview = defineAsyncComponent({
  loader: async () =>
    await import(
      `../../../../modules/${chatStore.getActiveModuleName}/resources/js/Components/MessagePreview.vue`
    ),
  loadingComponent: LoadingMessagePreview,
  errorComponent: ErrorMessagePreview,
  delay: 500,
  timeout: 5000
})
const getAiTemplates = () => {
  axios
    .get(route('user.conversations.api', { data: 'ai_templates' }))
    .then((res) => {
      chatStore.$patch({ aiTemplates: res.data.data })
    })
    .catch((err) => console.error(err))
}
onMounted(() => {
  getAiTemplates()
})
const assetStore = useAssetStore()
const chatStore = useChatStore()
const modalStore = useModalStore()

const {
  loading,
  assetPopup,
  inputMessage,
  activeConversation,
  quickReplySuggestionItems,
  activeConversationMessages
} = storeToRefs(chatStore)

const attachMedia = (mediaType) => {
  assetStore.openModal({
    load: mediaType,
    multiple: true,
    caption: ['audio', 'voice'].includes(mediaType) ? undefined : '',
    button: {
      text: 'Send'
    },
    onOpen: () => {
      assetPopup.value = false
      inputMessage.value.type = mediaType
    },
    onSelect: (asset) => {},
    onSubmit: (assets, state) => {
      inputMessage.value.attachments = assets.map((asset) => asset.path)
      inputMessage.value.caption = state.caption

      if (inputMessage.value.attachments?.length === 0) {
        return toast.warning('Please select at least one attachment')
      }

      chatStore.submitMessage()
    }
  })
}

const sendTextMessage = () => {
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
    <div
      class="styled-scrollbar whatsapp-chat-body relative h-[90%] overflow-auto px-4 pb-8"
      :class="`${$page.props.activeModule}-chat-body`"
    >
      <ul class="space-y-3" id="scrollContainerRef" v-if="activeConversationMessages?.length">
        <li v-if="activeConversation.no_more_messages">
          <p class="mt-4 text-center text-sm font-semibold italic text-gray-300">
            {{ trans('No more messages') }}
          </p>
        </li>
        <li class="text-center" v-else>
          <IntersectionObserver
            :loader="loading.messages"
            :afterIntersection="() => chatStore.loadMoreMessages()"
          />
        </li>

        <li
          v-for="(message, index) in activeConversationMessages"
          class="group"
          :key="index"
          :class="{ pr: message.direction == 'out' }"
        >
          <div class="flex gap-x-3 group-[.pr]:flex-row-reverse">
            <div v-if="message.direction == 'in'" class="avatar avatar-circle avatar-sm shrink-0">
              <img
                :src="activeConversation?.customer?.picture"
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
                <button
                  v-if="message.direction == 'in'"
                  @click="chatStore.setReplying(message)"
                  class="ms-1 rounded-full bg-gray-200 p-1 text-xl text-black hover:bg-gray-500"
                >
                  <Icon icon="bx:reply" />
                </button>
              </div>

              <div class="mb-2 space-x-2">
                <span
                  class="text-xs font-normal text-slate-400"
                  :title="moment(message.created_at).local().format('D MMM, YYYY h:mm A')"
                >
                  {{ moment(message.created_at).local().format('D MMM, h:mm A') }}
                </span>
                <span v-if="message.status == 'failed'" class="text-sm text-red-500">
                  {{ trans('message sending failed') }}
                </span>
                <span
                  v-if="message.direction == 'out'"
                  class="font-bolder ml-1 text-slate-300"
                  :title="
                    message.status == 'failed'
                      ? message.meta?.errors?.[0]?.error_data?.details
                      : message.status
                  "
                >
                  <i v-if="message.status == 'pending'" class="bx bx-circle"></i>
                  <i v-else-if="message.status == 'sent'" class="bx bx-check"></i>
                  <i v-else-if="message.status == 'delivered'" class="bx bx-check-double"></i>
                  <i
                    v-else-if="message.status == 'read'"
                    class="bx bx-check-double text-green-600"
                  ></i>
                  <i
                    v-else-if="message.status == 'failed'"
                    class="bx bx-info-circle text-red-600"
                  ></i>
                </span>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <div v-else class="h-200 flex items-center justify-center">
        <p class="text-center italic text-slate-600">
          {{ trans('No messages') }}
        </p>
      </div>
    </div>

    <div class="relative rounded-b-primary border-t bg-white dark:border-dark-700 dark:bg-dark-800">
      <div
        class="alert alert-danger flex justify-between p-2"
        v-if="chatStore.activeConversation?.meta?.is_24h_passed"
      >
        <p>
          {{
            trans(`This conversation is unavailable, more then 24 hours have passed since the customer
                    last replied to this number.`)
          }}
        </p>
        <button
          type="button"
          class="btn btn-sm btn-dark"
          @click="chatStore.activeConversation.meta.is_24h_passed = false"
        >
          {{ trans('Dismiss') }}
        </button>
      </div>
      <template v-else>
        <ReplyPreview v-if="chatStore.isReplying" />
        <VoiceRecorder v-if="inputMessage.type === 'voice'" :store="chatStore" />
        <QuickReplyPreview v-show="quickReplySuggestionItems.length > 0" />
        <form
          v-if="inputMessage.type != 'voice'"
          @submit.prevent="sendTextMessage"
          class="flex items-center rounded-primary shadow"
        >
          <CustomTextarea
            id="inputMessageField"
            v-model="inputMessage.message"
            placeholder="Type message... enter to send, shift+enter to next line"
            :disabled="loading.sendingMessage"
            @keydown.tab.prevent="chatStore.selectQuickReply"
            @keyup.down="chatStore.onArrowDown"
            @keydown.enter.exact.prevent="sendTextMessage"
            @keyup.up="chatStore.onArrowUp"
            autofocus
          />

          <div class="flex items-center gap-x-4 px-4">
            <button
              type="button"
              @click="modalStore.open('aiModal')"
              title="AI"
              class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
            >
              <Icon icon="fluent:chat-sparkle-16-regular" />
            </button>
            <button
              type="button"
              @click="chatStore.quickReplyModalOpen"
              title="Quick Reply"
              class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
            >
              <i class="bx bxs-layer"></i>
            </button>
            <button
              type="button"
              @click="modalStore.open(activeConversation.module + 'TemplateModal')"
              title="Templates"
              class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
            >
              <i class="bx bxs-grid"></i>
            </button>
            <button
              v-if="chatStore.features?.voice_message"
              type="button"
              @click="inputMessage.type = 'voice'"
              title="Voice"
              class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
            >
              <i class="bx bxs-microphone"></i>
            </button>
            <div class="relative">
              <button
                id="btn-upload-media"
                @click="assetPopup = !assetPopup"
                type="button"
                title="Media"
                class="text-slate-500 hover:text-primary-500 focus:text-primary-500 dark:text-slate-400 dark:hover:text-primary-500 dark:focus:text-primary-500"
              >
                <Icon icon="bx:link-alt"></Icon>
              </button>
              <div
                class="fixed inset-0 bg-transparent"
                v-if="assetPopup"
                @click="assetPopup = false"
              ></div>
              <transition name="fade">
                <div
                  v-if="assetPopup"
                  class="absolute bottom-full right-0 mb-2 space-y-2 rounded-md bg-dark-900 p-3"
                >
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
                </div>
              </transition>
            </div>
            <button type="submit">
              <DotLoader v-if="loading.sendingMessage" />
              <i v-else class="bx bx-send"></i>
            </button>
          </div>
        </form>
      </template>
    </div>
  </div>
</template>
