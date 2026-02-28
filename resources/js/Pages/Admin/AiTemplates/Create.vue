<script setup>
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import InputField from '@/Components/Forms/InputField.vue'
import SelectField from '@/Components/Forms/SelectField.vue'
import TextareaField from '@/Components/Forms/TextareaField.vue'
import InputImage from '@/Components/Forms/InputImage.vue'
import { useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import InputFieldError from '@/Components/InputFieldError.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps(['template', 'aiProviders', 'aiModels'])

const defaultInputFieldItem = {
  type: '',
  name: '',
  placeholder: '',
  value: ''
}

const form = useForm({
  title: '',
  description: '',
  preview: '',
  icon: '',
  status: '',
  ai_model: '',
  fields: [{ ...defaultInputFieldItem }],
  prompt: '',
  prompt_type: 'text',
  credit_charge: 0,
  meta: {
    provider: '',
    model: '',
    max_token: 0,
    max_word: null
  },
  _method: 'post'
})

const isEditing = !!props.template

onMounted(() => {
  if (isEditing) {
    form.title = props.template.title
    form.description = props.template.description
    form.preview = props.template.preview
    form.icon = props.template.icon
    form.status = props.template.status
    form.ai_model = props.template.ai_model
    form.fields = props.template.fields
    form.prompt = props.template.prompt
    form.prompt_type = props.template.prompt_type
    form.credit_charge = props.template.credit_charge
    form.meta = props.template.meta
  }
})

const aiModelsByProvider = computed(() => {
  return (props.aiModels ?? []).filter((item) => item.provider === form.meta.provider) ?? []
})

const addNewField = () => {
  form.fields.push({ ...defaultInputFieldItem })
}

const removeField = (index) => {
  form.fields.splice(index, 1)
}

const submit = () => {
  form._method = isEditing ? 'put' : 'post'
  form.post(
    isEditing
      ? route('admin.ai-templates.update', props.template.id)
      : route('admin.ai-templates.store')
  )
}

const bodyEl = ref()

const addToPrompt = (code) => {
  let curPos = bodyEl.value.selectionStart
  console.log(curPos)

  let text = form.prompt
  form.prompt = text.slice(0, curPos) + code + text.slice(curPos)

  let focusPosition = curPos + code.length
  setTimeout(() => {
    bodyEl.value?.setSelectionRange(focusPosition, focusPosition)
    bodyEl.value?.focus()
  }, 100)
}
</script>

<template>
  <div class="mx-auto lg:w-9/12">
    <form @submit.prevent="submit" class="space-y-4">
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            {{ isEditing ? trans('Edit Template') : trans('Create New Template') }}
          </div>
        </div>
        <div class="card-body">
          <div class="space-y-2">
            <InputField v-model="form.title" label="Title" :error="form.errors.title" />
            <textarea-field
              v-model="form.description"
              label="Description"
              :error="form.errors.description"
            />
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <input-image v-model="form.icon" label="Icon" :error="form.errors.icon" />
              <input-image v-model="form.preview" label="Preview" :error="form.errors.preview" />
              <select-field
                label="Status"
                :options="['active', 'draft']"
                v-model="form.status"
                :error="form.errors.status"
              />
              <InputField
                type="number"
                v-model="form.credit_charge"
                label="Credit Charge"
                :error="form.errors.credit_charge"
              />
            </div>
          </div>
        </div>
      </div>
      <!-- text -->
      <div v-if="form.prompt_type == 'text'" class="card">
        <div class="card-body">
          <div class="grid grid-cols-4 gap-2">
            <select-field
              label="Ai Provider"
              placeholder="Select Provider"
              v-model="form.meta.provider"
              option-label="title"
              option-value="id"
              :options="aiProviders"
              :error="form.errors['meta.provider']"
            />

            <select-field
              label="Ai Model"
              v-model="form.meta.model"
              option-label="name"
              option-value="id"
              placeholder="Select Model"
              :options="aiModelsByProvider"
              :error="form.errors['meta.model']"
            />

            <InputField
              type="number"
              v-model="form.meta.max_token"
              label="Max Token"
              :error="form.errors.max_token"
            />
            <InputField
              type="number"
              v-model="form.meta.max_word"
              label="Max Word"
              :error="form.errors['meta.max_word']"
            />
          </div>
        </div>
      </div>
      <!-- fields -->
      <div class="card">
        <div class="card-body">
          <div class="flex justify-between">
            <h5>{{ trans('Input Fields') }}</h5>
            <button type="button" @click="addNewField" class="btn btn-primary">+</button>
          </div>
          <div
            v-for="(field, index) in form.fields"
            :key="index"
            class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center"
          >
            <SelectField
              label="Field Type"
              v-model="field.type"
              :options="['input', 'textarea']"
              :error="form.errors['fields.' + index + '.type']"
            />
            <InputField
              label="Field Name"
              v-model="field.name"
              class="input"
              :error="form.errors['fields.' + index + '.name']"
            />
            <InputField
              label="Field Placeholder"
              v-model="field.placeholder"
              class="input"
              :error="form.errors['fields.' + index + '.placeholder']"
            />
            <button type="button" class="btn btn-danger mt-6" @click="removeField(index)">X</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <div class="mb-2">
            <label class="label">Prompt</label>
            <textarea v-model="form.prompt" ref="bodyEl" class="textarea" />
            <InputFieldError :message="form.errors.prompt" />
          </div>
          <ul class="mb-3 flex flex-wrap items-center gap-1">
            <li
              v-for="code in form.fields
                .filter((item) => item.name.length)
                .map((item) => `[${item.name}]`)"
              @click="addToPrompt(code)"
              :key="code"
              class="rounded border px-2 py-1 dark:border-dark-700 dark:bg-dark-700 dark:hover:bg-opacity-80"
            >
              {{ code }}
            </li>
          </ul>
          <div class="mb-2">
            <spinner-btn
              :processing="form.processing"
              :btn-text="isEditing ? trans('Update') : trans('Create')"
            />
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
