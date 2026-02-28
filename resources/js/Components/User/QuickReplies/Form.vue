<script setup>
import { onMounted } from 'vue'

import { useForm } from '@inertiajs/vue3'
import SelectField from '@/Components/Forms/SelectField.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

const props = defineProps({
  module: {
    type: String,
    required: true
  },
  platformLevel: {
    type: String,
    default: 'Platform'
  },
  platforms: {
    type: Array,
    required: []
  },
  shortCodes: {
    type: Array,
    default: []
  },
  quickReply: {
    type: Object,
    default: {}
  }
})

const isEditing = !!props.quickReply?.id

const form = useForm({
  module: props.module,
  platform_id: '',
  message_template: '',
  status: 'active',
  _method: 'POST'
})

onMounted(() => {
  if (isEditing && props.quickReply) {
    form.platform_id = props.quickReply.platform_id
    form.message_template = props.quickReply.message_template
    form.status = props.quickReply.status
    form._method = 'PUT'
  }
})

const handleFormSubmit = () => {
  form.post(
    route(`user.${props.module.toLowerCase()}.quick-replies.${isEditing ? 'update' : 'store'}`, {
      id: isEditing ? props.quickReply.id : null
    })
  )
}

const statuses = [
  {
    name: 'Active',
    id: 'active'
  },
  {
    name: 'Inactive',
    id: 'inactive'
  }
]

const addShortCodeToMessageTemplate = (code) => {
  form.message_template = `${form.message_template} ${code}`
}
</script>
<template>
  <form @submit.prevent="handleFormSubmit">
    <div class="card card-body mx-auto space-y-3 py-6 sm:max-w-2xl">
      <SelectField
        :label="platformLevel"
        v-model="form.platform_id"
        :validationMessage="form.errors?.platform_id"
        :options="platforms"
      />

      <div>
        <label class="label mb-1">{{ trans('Message Template') }}</label>
        <textarea
          v-model="form.message_template"
          id="body-text-id"
          :class="{
            'border border-danger-600': form.errors?.text
          }"
          class="textarea"
        ></textarea>
        <div v-if="form.errors?.text" class="my-1 text-[11px] font-light text-danger-600">
          {{ form.errors?.text }}
        </div>
      </div>

      <div class="flex items-center gap-3">
        <p>Variables:</p>
        <div class="flex items-center gap-2">
          <template v-for="(code, index) in shortCodes" :key="index">
            <button
              type="button"
              class="btn-outline-primary rounded px-1"
              @click="addShortCodeToMessageTemplate(code)"
            >
              {{ code }}
            </button>
          </template>
        </div>
      </div>

      <SelectField
        label="Select Status"
        v-model="form.status"
        placeholder="SELECT"
        :validationMessage="form.errors?.status"
        :options="statuses"
      />

      <div class="flex w-full justify-end">
        <SpinnerBtn
          type="submit"
          :processing="form.processing"
          :btn-text="trans(isEditing ? 'Update' : 'Create')"
        />
      </div>
    </div>
  </form>
</template>
