<script setup>
import { computed, ref, onMounted } from 'vue'
import { useModalStore } from '@/Store/modalStore'
import axios from 'axios'
import Modal from '@/Components/Dashboard/Modal.vue'
import OverviewGrid from '@/Components/Dashboard/OverviewGrid.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import InputField from '@/Components/Forms/InputField.vue'
import sharedComposable from '@/Composables/sharedComposable'
import toast from '@/Composables/toastComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import MultiSelect from '@vueform/multiselect'
import RangeSlider from '@/Components/RangeSlider.vue'
import { useForm } from '@inertiajs/vue3'
import moment from 'moment'

const props = defineProps(['platforms', 'warmer'])
defineOptions({ layout: UserLayout })
const modalStore = useModalStore()
const { badgeClass } = sharedComposable()

const automationConfig = ref({
  selectedDevices: [],
  messageDelay: [1, 100],
  isRunning: false,
  isPaused: false,
  currentFlowIndex: 0
})

const conversationFlow = ref([])

//  Q&A data
const qaList = ref([
  {
    id: 1,
    question: 'Hi, how can I help you?',
    answer: 'I need support',
    deviceId: null,
    type: 'demo'
  },
  {
    id: 2,
    question: 'What type of service are you interested in?',
    answer: 'Website Development',
    deviceId: null,
    type: 'demo'
  },
  {
    id: 3,
    question: 'Would you like to schedule a consultation?',
    answer: 'Yes, please',
    deviceId: null,
    type: 'demo'
  }
])

const overviews = computed(() => {
  return [
    {
      title: 'Total Messages',
      value: conversationFlow.value.length,
      icon: 'bx:message'
    },
    {
      title: 'Messages Sent',
      value: conversationFlow.value.filter((msg) => msg.status === 'sent').length,
      icon: 'bx:check-double'
    },
    {
      title: 'Pending Messages',
      value: conversationFlow.value.filter((msg) => msg.status === 'pending').length,
      icon: 'bx:time'
    }
  ]
})

onMounted(() => {
  modalStore.open('automationConfig')
  if (props.warmer?.dataset && props.warmer.dataset.length) {
    qaList.value = props.warmer.dataset
  }
})

const addQA = () => {
  qaList.value.push({
    id: Date.now(),
    question: '',
    answer: '',
    deviceId: null,
    type: 'custom'
  })
}

const removeQA = (index) => {
  qaList.value.splice(index, 1)
}

const duplicateQA = (qa) => {
  qaList.value.push({
    ...qa,
    id: Date.now(),
    type: 'custom',
    question: `${qa.question} (Copy)`
  })
}

const generateConversationFlow = () => {
  if (qaList.value.length === 0) {
    toast.danger('Please add at least one Q&A item')
    return
  }

  const platforms = automationConfig.value.selectedDevices

  if (platforms.length < 2) {
    toast.danger('Please select at least 2 platforms')
    return
  }

  conversationFlow.value = []

  let currentSenderIndex = 0
  let currentReceiverIndex = 1

  qaList.value.forEach((qa) => {
    conversationFlow.value.push({
      id: `${qa.id}-q`,
      message: qa.question,
      type: 'question',
      deviceId: platforms[currentSenderIndex],
      receiverId: platforms[currentReceiverIndex],
      originQA: qa.id,
      status: 'pending',
      timestamp: null
    })

    currentSenderIndex = (currentSenderIndex + 1) % platforms.length
    currentReceiverIndex = (currentReceiverIndex + 1) % platforms.length

    // Answer message
    conversationFlow.value.push({
      id: `${qa.id}-a`,
      message: qa.answer,
      type: 'answer',
      deviceId: platforms[currentSenderIndex],
      receiverId: platforms[currentReceiverIndex],
      originQA: qa.id,
      status: 'pending',
      timestamp: null
    })

    // Rotate to the next sender and receiver
    currentSenderIndex = (currentSenderIndex + 1) % platforms.length
    currentReceiverIndex = (currentReceiverIndex + 1) % platforms.length
  })
}

const startAutomation = () => {
  if (automationConfig.value.selectedDevices.length < 2) {
    toast.danger('Please select at least 2 platforms for conversation flow')
    return
  }
  if (form.hasErrors) {
    toast.danger('Please clear Q&A form errors')
    return
  }

  if (conversationFlow.value.length > 0) {
    conversationFlow.value = []
  }

  generateConversationFlow()
  automationConfig.value.isRunning = true
  automationConfig.value.isPaused = false
  processNextMessage()
}

const pauseAutomation = () => {
  automationConfig.value.isPaused = true
}

const resumeAutomation = () => {
  automationConfig.value.isPaused = false
  processNextMessage()
}

const stopAutomation = () => {
  automationConfig.value.isRunning = false
  automationConfig.value.isPaused = false
  automationConfig.value.currentFlowIndex = 0
}

const processNextMessage = () => {
  if (!automationConfig.value.isRunning || automationConfig.value.isPaused) return

  if (automationConfig.value.currentFlowIndex >= conversationFlow.value.length) {
    stopAutomation()
    toast.success('Automation completed')
    return
  }

  const currentMessage = conversationFlow.value[automationConfig.value.currentFlowIndex]

  const senderId = currentMessage.deviceId
  const receiverId = currentMessage.receiverId

  const delay =
    Math.floor(Math.random() * automationConfig.value.messageDelay[1]) +
    automationConfig.value.messageDelay[0]

  axios
    .post(route('user.whatsapp-web.warmer.send-message'), {
      sender_id: senderId,
      device_id: receiverId,
      message: currentMessage.message,
      delay
    })
    .then(() => {
      conversationFlow.value[automationConfig.value.currentFlowIndex].status = 'sent'
      conversationFlow.value[automationConfig.value.currentFlowIndex].timestamp = new Date()

      automationConfig.value.currentFlowIndex++
      processNextMessage()
    })
    .catch(() => {
      toast.danger('Failed to send message')
    })
}

const form = useForm({
  title: props.warmer.title,
  dataset: []
})

const submit = () => {
  form.dataset = qaList.value
  form.put(route('user.whatsapp-web.warmer.update', { warmer: props.warmer.id }))
}
</script>

<template>
  <OverviewGrid :items="overviews" :grid="3" />
  <div class="mb-4 flex justify-end gap-2">
    <SpinnerBtn
      v-if="!automationConfig.isRunning"
      @click="startAutomation"
      btn-text="Start Automation"
      classes="btn btn-success"
      icon="bx:play"
    />
    <SpinnerBtn
      v-if="automationConfig.isRunning && !automationConfig.isPaused"
      @click="pauseAutomation"
      btn-text="Pause"
      classes="btn btn-warning"
      icon="fe:pause"
    />
    <SpinnerBtn
      v-if="automationConfig.isRunning && automationConfig.isPaused"
      @click="resumeAutomation"
      btn-text="Resume"
      classes="btn btn-info"
      icon="fe:play"
    />
    <SpinnerBtn
      v-if="automationConfig.isRunning && !automationConfig.isPaused"
      :processing="automationConfig.isRunning"
      @click="stopAutomation"
      btn-text="Stop"
      classes="btn btn-danger"
      icon="fe:stop"
    />
  </div>

  <div class="table-responsive mt-3 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Sender') }}</th>
          <th>{{ trans('Receiver') }}</th>
          <th>{{ trans('Message Type') }}</th>
          <th>{{ trans('Message') }}</th>
          <th>{{ trans('Status') }}</th>
          <th>{{ trans('Timestamp') }}</th>
        </tr>
      </thead>
      <tbody v-if="conversationFlow.length" class="tbody">
        <tr
          v-for="msg in conversationFlow"
          :key="msg.id"
          :class="{ 'bg-gray-50': msg.type === 'answer' }"
        >
          <td>
            {{ props.platforms.find((d) => d.id === msg.deviceId)?.name }}
          </td>
          <td>
            {{ props.platforms.find((d) => d.id === msg.receiverId)?.name }}
          </td>
          <td>
            <span>
              {{ msg.type }}
            </span>
          </td>
          <td>{{ msg.message }}</td>
          <td>
            <span :class="badgeClass(msg.status)">
              {{ msg.status }}
            </span>
          </td>
          <td>
            {{
              msg.timestamp ? moment(msg.timestamp).format('DD MMM, YYYY h:mm A') : trans('Waiting')
            }}
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
  </div>

  <!-- Modal -->
  <Modal state="automationConfig" header-title="Configure Warmer Devices" :header-state="true">
    <div class="space-y-4">
      <div>
        <label class="label mb-1"
          >{{ trans('Select Devices for Conversation') }}
          <span class="text-xs text-primary-500"
            >( {{ trans('Only verified number') }} )</span
          ></label
        >
        <MultiSelect
          mode="tags"
          class="multiselect-dark"
          v-model="automationConfig.selectedDevices"
          placeholder="Select Devices for Conversation"
          valueProp="id"
          label="name"
          :options="platforms"
        />
      </div>
      <div>
        <label for="device_rotation_duration" class="label mb-1">
          {{ trans('Message Delay (in seconds)') }}
        </label>
        <RangeSlider class="px-1" v-model="automationConfig.messageDelay" :step="2" />
      </div>
    </div>
  </Modal>

  <!-- Q&A Modal -->
  <Modal state="qaConfig" header-title="Configure Q&A Flow" :header-state="true">
    <div class="styled-scrollbar max-h-[80vh] overflow-y-auto pb-3 pr-2">
      <div class="space-y-3">
        <div
          v-for="(qa, qaIndex) in qaList"
          :key="qa.id"
          class="relative rounded-lg border p-4 transition-shadow hover:shadow-md dark:border-slate-600"
        >
          <div class="mb-1 flex items-center justify-between">
            <span class="badge" :class="qa.type === 'demo' ? 'badge-secondary' : 'badge-primary'">
              {{ qa.type === 'demo' ? 'Demo' : 'User' }}
            </span>
            <div class="flex gap-2">
              <button class="btn btn-sm btn-info" @click="duplicateQA(qa)">
                <Icon icon="bx:copy" />
              </button>
              <button class="btn btn-sm btn-danger" @click="removeQA(qaIndex)">
                <Icon icon="bx:trash" />
              </button>
            </div>
          </div>
          <div class="space-y-1">
            <InputField
              v-model="qa.question"
              placeholder="Enter question"
              label="Question Message"
              :error="form.errors[`dataset.${qaIndex}.question`]"
            />

            <InputField
              v-model="qa.answer"
              placeholder="Enter answer"
              label="Answer Message"
              :error="form.errors[`dataset.${qaIndex}.answer`]"
            />
          </div>
        </div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button class="btn btn-primary" @click="addQA">
          <Icon icon="bx:plus" />
          <span>{{ trans('Add New Q&A') }}</span>
        </button>
        <SpinnerBtn
          :processing="form.processing"
          class="btn btn-success"
          @click="submit"
          type="button"
          :btn-text="trans('Save')"
          icon="bx:save"
        />
      </div>
    </div>
  </Modal>
</template>
