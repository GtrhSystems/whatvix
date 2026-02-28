<script setup>
import Modal from '@/Components/Dashboard/Modal.vue'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import { useModalStore } from '@/Store/modalStore'

const chatStore = useChatStore()
const modalStore = useModalStore()
</script>

<template>
  <!-- Quick reply Modal start -->
  <Modal :header-state="true" header-title="Quick reply" state="quickReplyModal">
    <div class="group mb-2 mt-3 flex items-center rounded-lg">
      <input
        type="text"
        placeholder="Search quick reply templates"
        v-model="chatStore.quickReplySearchInput"
        @keydown.down="chatStore.onArrowDown"
        @keydown.up="chatStore.onArrowUp"
        @keydown.enter.prevent="chatStore.selectQuickReply"
        @keydown.tab.prevent="chatStore.selectQuickReply"
        @keyup.esc="chatStore.quickReplyModalClose"
        class="input"
        :ref="chatStore.quickReplyModalSearchInput"
      />
    </div>

    <div class="styled-scrollbar max-h-[560px] space-y-2 overflow-y-auto">
      <div class="mt-1 h-[22rem]">
        <template v-if="chatStore.quickReplyFilteredItems.length > 0">
          <div
            v-for="(item, index) of chatStore.quickReplyFilteredItems"
            @click="chatStore.addQuickReplyToMessageInput(item)"
            :key="index"
          >
            <div
              class="my-1 cursor-pointer rounded border px-1 py-2 text-sm shadow-sm hover:border-primary-300 hover:bg-primary-100 dark:bg-dark-700 dark:hover:bg-opacity-80"
              :key="index"
              :class="{
                'border border-primary-300 bg-primary-50': chatStore.arrowCounter == index
              }"
              @click="chatStore.addQuickReplyToMessageInput(item)"
            >
              {{ item }}
            </div>
          </div>
        </template>
        <div v-else class="text-center text-gray-700">
          {{ trans('No record found') }}
          <template v-if="chatStore.quickReplySearchInput">
            for "<b> {{ chatStore.quickReplySearchInput }}</b
            >"
          </template>
        </div>
      </div>
    </div>
  </Modal>
  <!-- Quick reply Modal end -->
</template>
