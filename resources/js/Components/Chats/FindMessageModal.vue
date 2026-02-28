<script setup>
import Modal from '@/Components/Dashboard/Modal.vue'
import LoadingMessagePreview from './LoadingMessagePreview.vue'
import ErrorMessagePreview from './ErrorMessagePreview.vue'
import { useChatStore } from '@/Store/chatStore'
import { defineAsyncComponent } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'
import moment from 'moment'

const { debounce } = sharedComposable()

const chatStore = useChatStore()

const DynamicMessagePreview = defineAsyncComponent({
  loader: async () =>
    await import(
      `../../../../modules/${chatStore.getActiveModuleName}/resources/js/Components/MessagePreview.vue`
    ),
  loadingComponent: LoadingMessagePreview,
  errorComponent: ErrorMessagePreview,
  timeout: 5000
})

const searchMessage = debounce(() => {
  chatStore.getChatSearchedItems()
}, 500)

const focusElement = (element) => {
  if (element) {
    setTimeout(() => {
      element.focus()
    }, 300)
  }
}
</script>

<template>
  <Modal :header-state="true" header-title="Search Message" state="findMessageModal">
    <div class="group mb-2 mt-3 flex items-center rounded-lg">
      <input
        type="text"
        class="input"
        placeholder="enter search query"
        v-model="chatStore.chatSearchInput"
        @keyup="searchMessage"
        :ref="focusElement"
      />
    </div>

    <div class="styled-scrollbar max-h-[560px] space-y-2 overflow-y-auto">
      <div class="mt-1 h-[22rem]">
        <div
          class="my-1 rounded px-2 py-2 text-sm shadow-sm hover:bg-primary-100 dark:bg-dark-700 dark:hover:bg-opacity-80"
          v-for="(message, index) of chatStore.chatSearchedItems"
          :key="index"
        >
          <DynamicMessagePreview :message="message" />
          <div class="flex justify-between text-end text-xs font-normal text-slate-400">
            <span>{{ message.direction == 'in' ? 'Received' : 'Sent' }} </span>
            <span>
              {{ moment(message.created_at).local().format('D MMM, YYYY h:mm A') }}
            </span>
          </div>
        </div>
        <p
          class="mt-5 text-center text-lg"
          v-if="
            !chatStore.loading.message_searching &&
            chatStore.chatSearchedItems.length === 0 &&
            chatStore.chatSearchInput.length > 0
          "
        >
          {{ trans('No record found for: ') }}
          <strong>"{{ chatStore.chatSearchInput }}"</strong>
        </p>
        <p class="mt-5 text-center text-lg" v-if="chatStore.loading.message_searching">
          {{ trans('Searching...') }}
        </p>
        <img
          class="p-16"
          v-if="chatStore.chatSearchedItems.length === 0 && chatStore.chatSearchInput.length === 0"
          src="/assets/svg/search_vimp.svg"
          alt=""
        />
      </div>
    </div>
  </Modal>
</template>
