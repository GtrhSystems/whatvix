<script setup>
import { computed, ref } from 'vue'

import TemplatePreview from '@whatsappWeb/Pages/Templates/Partials/TemplatePreview.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()
const chatStore = useChatStore()

const selectedTemplateId = ref('')
const selectedTemplate = computed(
  () => chatStore.chatTemplates.find((t) => t.id == selectedTemplateId.value) ?? {}
)

const sendTemplateMessage = () => {
  chatStore.inputMessage.type = 'template'
  chatStore.inputMessage.template = selectedTemplate.value
  chatStore.submitMessage()
}
</script>

<template>
  <Modal
    :header-state="true"
    header-title="Send Template"
    state="TemplateMessageModal"
    modalSize="w-xxl"
  >
    <div class="flex w-full flex-col items-center gap-y-6">
      <div
        class="whatsapp-chat-body relative mt-3 h-[33rem] w-80 rounded-xl border-2 border-dark-400 bg-cover outline outline-4 outline-dark-500 dark:border-dark-800 dark:outline-dark-950"
      >
        <div
          class="absolute bottom-3 left-4 w-10/12 rounded-lg bg-white p-1 px-2 dark:bg-dark-700 xl:w-8/12"
        >
          <TemplatePreview :template="selectedTemplate" />
        </div>
      </div>
      <div class="flex w-full items-center gap-2 rounded-lg">
        <select v-model="selectedTemplateId" class="select">
          <option value="">{{ trans('Choose Template') }}</option>
          <option
            v-for="item in chatStore.getActiveModuleTemplates"
            :key="item.id"
            :value="item.id"
          >
            {{ item.name }}
          </option>
        </select>
        <SpinnerBtn
          @click="sendTemplateMessage"
          type="button"
          :disabled="!selectedTemplateId"
          :processing="chatStore.loading.sendingMessage"
          :btn-text="chatStore.loading.sendingMessage ? 'Sending...' : 'Send'"
          icon="bx:send"
        />
      </div>
    </div>
  </Modal>
</template>
