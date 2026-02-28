<script setup>
import { computed, ref } from 'vue'

import Modal from '@/Components/Dashboard/Modal.vue'
import Preview from '@/Components/Preview.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useChatStore } from '@/Store/chatStore'
import InteractiveCreateForm from '@whatsapp/Components/Interactive/CreateForm.vue'
import InteractivePreview from '@whatsapp/Components/Preview/InteractiveMessage.vue'
import TemplateComponent from '@whatsapp/Components/Template/TemplateComponent.vue'

const chatStore = useChatStore()

const selectedTemplateId = ref('')
const selectedTemplate = computed(() =>
  chatStore.chatTemplates.find((item) => item.id === selectedTemplateId.value)
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
    header-title="Message Template"
    state="whatsappTemplateModal"
    state-key="whatsappTemplateModal"
    modalSize="w-full h-full"
  >
    <div class="h-[calc(100vh-5rem)] space-y-2 overflow-y-auto">
      <div class="mt-3 p-1">
        <div class="grid grid-cols-1 place-items-start gap-4 lg:grid-cols-3">
          <div class="col-span-full w-full lg:col-span-2">
            <div class="group mb-5 flex items-center rounded-lg dark:bg-slate-700">
              <select v-model="selectedTemplateId" class="select w-full">
                <option value="">Choose Template</option>
                <option
                  v-for="item in chatStore.getActiveModuleTemplates"
                  :key="item.id"
                  :value="item.id"
                >
                  {{ item.name }}
                </option>
              </select>
            </div>
            <div class="styled-scrollbar h-[calc(100vh-10rem)] overflow-y-auto">
              <template v-if="selectedTemplate?.type == 'interactive'">
                <InteractiveCreateForm :meta="selectedTemplate.meta" :preview="false" />
              </template>
              <template v-else>
                <TemplateComponent :components="selectedTemplate?.meta.components" />
              </template>
            </div>
          </div>
          <div
            class="col-span-full w-full rounded-md bg-slate-200 p-2 dark:bg-dark-800 lg:col-span-1"
          >
            <p class="my-4">{{ trans('Preview') }}</p>
            <div class="card-body whatsapp-chat-body">
              <InteractivePreview
                v-if="selectedTemplate?.type == 'interactive'"
                :components="selectedTemplate.meta"
              />
              <Preview v-else :templateData="selectedTemplate?.meta.components" />
            </div>
            <div class="card-footer">
              <SpinnerBtn
                v-if="selectedTemplate?.id"
                @click="sendTemplateMessage"
                type="button"
                :processing="chatStore.loading.sendingMessage"
                class="btn btn-primary float-right mt-2"
                btn-text="Send"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>
