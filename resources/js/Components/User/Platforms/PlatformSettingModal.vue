<script setup>
import { watch, computed } from 'vue'

import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import { useModalStore } from '@/Store/modalStore'

const { copyToClipboard } = sharedComposable()

const modalStore = useModalStore()

const props = defineProps({
  platform: {
    type: Object,
    required: true
  },
  routeName: {
    type: String,
    required: true
  },
  autoReplyMethods: {
    type: Array,
    default: ['default']
  },
  trainedAiModels: {
    type: Array,
    default: []
  },
  chatFlows: {
    type: Array,
    default: []
  },
  autoResponses: {
    type: Array,
    default: []
  },
  module: {
    type: String,
    default: null
  },
  fields: {
    type: Array,
    default: []
  }
})

const form = useForm({
  name: '',
  access_token: '',
  send_auto_reply: false,
  auto_reply_method: '',
  trained_ai: '',
  chat_flow: '',
  auto_response: '',
  send_welcome_message: false,
  welcome_message_template: '',

  // optional fields
  phone_number_id: '',
  waba_id: '',
  business_id: '',
  module: props.module
})

watch(
  () => props.platform.id,
  () => {
    if (props.platform?.uuid) {
      let meta = props.platform.meta ?? {}
      form.name = props.platform.name
      form.access_token = props.platform.access_token
      form.send_auto_reply = meta.send_auto_reply
      form.auto_reply_method = meta.auto_reply_method
      form.trained_ai = meta.trained_ai
      form.chat_flow = meta.chat_flow
      form.auto_response = meta.auto_response
      form.send_welcome_message = meta.send_welcome_message
      form.welcome_message_template = meta.welcome_message_template

      // optional fields
      form.phone_number_id = meta.phone_number_id
      form.waba_id = meta.waba_id
      form.business_id = meta.business_account_id
    }
  }
)

const submit = () => {
  form.put(route(props.routeName, props.platform.uuid), {
    onSuccess: () => {
      modalStore.close('platformSettingModal')
    }
  })
}

const hasField = (field) => {
  return props.fields.includes(field)
}

const getAutoReplyMethods = computed(() => {
  let methods = ['default']

  if (props.trainedAiModels.length > 0) {
    methods.push('trained_ai')
  }

  if (props.chatFlows.length > 0) {
    methods.push('chat_flow')
  }

  if (props.autoResponses.length > 0) {
    methods.push('auto_response')
  }

  return methods
})
</script>

<template>
  <Modal :header-state="true" header-title="Platform Setting" state="platformSettingModal">
    <form @submit.prevent="submit">
      <!-- webhook_url -->
      <div class="mb-2" v-if="hasField('webhook_url')">
        <label for="webhook" class="label">{{ trans('Callback URL (read-only)') }}</label>
        <div class="flex gap-1">
          <input type="text" class="input" disabled :value="platform.webhook_url" />
          <button
            type="button"
            class="btn btn-secondary"
            @click="copyToClipboard(platform.webhook_url)"
          >
            <Icon icon="bx:copy" class="text-xl" />
          </button>
        </div>
      </div>

      <!-- name -->
      <div class="mb-2" v-if="hasField('name')">
        <label for="name" class="label">{{ trans('Platform Name') }}</label>
        <input type="text" class="input" v-model="form.name" />
      </div>

      <div class="flex gap-2">
        <!-- phone_number_id -->
        <div class="mb-2" v-if="hasField('phone_number_id')">
          <label for="phone_number_id" class="label">{{ trans('Phone Number ID') }}</label>
          <input type="text" class="input" :value="form.phone_number_id" disabled />
        </div>

        <!-- waba_id -->
        <div class="mb-2" v-if="hasField('waba_id')">
          <label for="waba_id" class="label">{{ trans('Waba ID') }}</label>
          <input type="text" class="input" :value="form.waba_id" disabled />
        </div>

        <!-- business_id -->
        <div class="mb-2" v-if="hasField('business_id')">
          <label for="business_id" class="label">{{ trans('Business ID') }}</label>
          <input type="text" class="input" :value="form.business_id" disabled />
        </div>
      </div>

      <!-- access_token -->
      <div class="mb-2" v-if="hasField('access_token')">
        <label for="access_token" class="label">{{ trans('Access Token') }}</label>
        <input type="text" class="input" v-model="form.access_token" />
      </div>

      <p class="text-md mb-2 mt-4 font-bold">{{ trans('Auto Reply Settings') }}</p>

      <div class="mb-2">
        <label>{{ trans('Send auto reply') }}</label>
        <select class="select" v-model="form.send_auto_reply">
          <option :value="true">{{ trans('Yes') }}</option>
          <option :value="false">{{ trans('No') }}</option>
        </select>
        <small>{{ trans('Enable or disable auto reply') }}</small>
      </div>

      <div class="mb-2" v-if="form.send_auto_reply == true">
        <label>{{ trans('Auto reply method') }}</label>
        <select class="select" v-model="form.auto_reply_method">
          <option v-for="method in getAutoReplyMethods" :value="method" :key="method">
            {{ method.replace('_', ' ') }}
          </option>
        </select>
        <small>{{ trans('How message will be replied') }}</small>
      </div>

      <template v-if="form.send_auto_reply == true">
        <div class="mb-2" v-if="form.auto_reply_method == 'trained_ai'">
          <label>{{ trans('Trained AI') }}</label>
          <select class="select" v-model="form.trained_ai">
            <option v-for="item in trainedAiModels" :value="item.id" :key="item.id">
              {{ item.title }}
            </option>
          </select>
          <small>{{ trans('The AI model will be used') }}</small>
        </div>

        <div class="mb-2" v-if="form.auto_reply_method == 'chat_flow'">
          <label>{{ trans('Chat Flow') }}</label>
          <select class="select" v-model="form.chat_flow">
            <option v-for="flow in chatFlows" :value="flow.id" :key="flow.id">
              {{ flow.title }}
            </option>
          </select>
          <small>{{ trans('The chat flow will be used') }}</small>
        </div>

        <div class="mb-2" v-if="form.auto_reply_method == 'auto_response'">
          <label>{{ trans('Auto response Dataset') }}</label>
          <select class="select" v-model="form.auto_response">
            <option v-for="autoRes in autoResponses" :value="autoRes.id" :key="autoRes.id">
              {{ autoRes.title }}
            </option>
          </select>
          <small>{{ trans('The auto response dataset will be used') }}</small>
        </div>
      </template>

      <!-- toggle welcome message -->
      <div class="mb-2">
        <label>{{ trans('Send Welcome message') }}</label>
        <select class="select" v-model="form.send_welcome_message">
          <option :value="true">{{ trans('Yes') }}</option>
          <option :value="false">{{ trans('No') }}</option>
        </select>
        <div class="mt-2" v-if="form.send_welcome_message === true">
          <textarea v-model="form.welcome_message_template" class="input" rows="5"></textarea>
          <small>{{ trans('This message will be sent to new users') }}</small>
        </div>
      </div>

      <div class="mt-2 text-end">
        <SpinnerBtn
          classes="btn btn-primary"
          :processing="form.processing"
          :btn-text="trans('Submit')"
        />
      </div>
    </form>
  </Modal>
</template>
