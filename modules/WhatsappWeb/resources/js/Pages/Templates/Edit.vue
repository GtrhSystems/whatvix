<script setup>
import { ref, watch, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputField from '@/Components/Forms/InputField.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import TemplatePreview from '@whatsappWeb/Pages/Templates/Partials/TemplatePreview.vue'
import ShortCodes from '@/Components/Forms/ShortCodes.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['template'])

const activeTab = ref(props.template.type ?? 'text')
const form = useForm({
  name: '',
  type: 'text',
  meta: {},
  _method: 'put'
})

const tabButtons = [
  { label: 'Text Message', value: 'text' },
  { label: 'Text With image', value: 'image' },
  { label: 'Text With video', value: 'video' },
  { label: 'Audio Message', value: 'audio' },
  { label: 'Send Location', value: 'location' },
  { label: 'Poll Message', value: 'poll' },
  { label: 'Document Message', value: 'document' }
]

const templateMessageFormFields = {
  text: { text: '' },
  poll: { name: '', values: ['', ''], selectableCount: 1 },
  video: { video: null, caption: '', gifPlayback: true },
  image: { image: null, caption: '' },
  audio: { audio: null },
  location: { latitude: '', longitude: '' },
  document: { document: null, caption: '' }
}

const setTemplateForm = (type) => {
  form.type = type
  form.meta = JSON.parse(JSON.stringify(templateMessageFormFields[type]))
}

const submitForm = () => {
  form.post(route('user.whatsapp-web.templates.update', props.template.id))
}

watch(activeTab, setTemplateForm)

onMounted(() => {
  // Initialize form with template data
  form.name = props.template.name
  form.type = props.template.type
  form.meta = props.template.meta
})

const handleFileInput = (event, field) => {
  const file = event.target.files[0]
  if (file) {
    form.meta[field] = file
    const url = URL.createObjectURL(file)
    if (field === 'document') {
      form.meta.document_name = file.name
    }
    if (['video', 'image', 'audio'].includes(field)) {
      form.meta[`${field}Url`] = url
    }
  }
}

const getFieldType = (field, value) => {
  if (field === 'values') return 'array'
  if (['video', 'image', 'audio', 'document'].includes(field)) return 'file'
  if (typeof value === 'boolean' || field === 'gifPlayback') return 'checkbox'
  if (typeof value === 'number') return 'number'
  if (['latitude', 'longitude', 'name', 'caption'].includes(field)) return 'input'
  if (field === 'text') return 'textarea'
  return 'input'
}

const addPollOption = () => {
  form.meta.values.push('')
}

const removePollOption = (index) => {
  form.meta.values.splice(index, 1)
}

const transformText = (field, symbol) => {
  const textarea = document.querySelector(`textarea[name="meta.${field}"]`)
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selectedText = form.meta[field].substring(start, end)

  const isWrapped = selectedText.startsWith(symbol) && selectedText.endsWith(symbol)
  const newText = isWrapped
    ? selectedText.slice(symbol.length, -symbol.length)
    : symbol + selectedText + symbol

  form.meta[field] =
    form.meta[field].substring(0, start) + newText + form.meta[field].substring(end)

  const newSelectionStart = start + (isWrapped ? -symbol.length : symbol.length)
  const newSelectionEnd = end + (isWrapped ? -symbol.length : symbol.length)
  textarea.setSelectionRange(newSelectionStart, newSelectionEnd)
}

const textTransformButtons = [
  { label: 'Italic', symbol: '_' },
  { label: 'Monospace', symbol: '```' },
  { label: 'Strike', symbol: '~' },
  { label: 'Bold', symbol: '*' }
]
</script>

<template>
  <div class="card flex max-w-max flex-wrap gap-2 rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
    <template v-for="item in tabButtons" :key="item.value">
      <button
        v-if="item.value == template.type"
        class="btn-xs flex-1 whitespace-nowrap rounded-md px-8 py-2 hover:bg-primary-100 dark:hover:bg-primary-700/50 md:flex-initial"
        :class="{
          'bg-primary-600 text-white hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-800':
            item.value === activeTab,
          'bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-200': item.value !== activeTab
        }"
        @click="activeTab = item.value"
      >
        <span class="text-xs md:text-sm">{{ item.label }}</span>
      </button>
    </template>
  </div>

  <div class="mt-4 grid grid-cols-1 place-items-start gap-10 md:grid-cols-12">
    <div class="card card-body md:col-span-9">
      <form @submit.prevent="submitForm" class="space-y-2">
        <InputField v-model="form.name" label="Name" />

        <template v-for="(value, field) in form.meta" :key="field">
          <div v-if="getFieldType(field, value) === 'file'">
            <label class="label mb-1 capitalize">{{ field }}</label>
            <input
              type="file"
              :accept="`${field}/*`"
              @input="(event) => handleFileInput(event, field)"
              class="input"
            />
          </div>

          <div v-else-if="getFieldType(field, value) === 'checkbox'">
            <label class="label mb-1 capitalize">
              <input
                type="checkbox"
                v-model="form.meta[field]"
                class="mr-2"
                :checked="form.meta[field] == 1"
              />
              {{ field }}
            </label>
          </div>

          <InputField
            v-else-if="getFieldType(field, value) === 'number'"
            v-model="form.meta[field]"
            :label="field.charAt(0).toUpperCase() + field.slice(1)"
            type="number"
            step="any"
          />

          <div v-else-if="getFieldType(field, value) === 'array'">
            <label class="label mb-1 block capitalize">{{ field }}</label>
            <div
              v-for="(option, index) in form.meta[field]"
              :key="index"
              class="mb-2 flex items-center"
            >
              <input
                v-model="form.meta[field][index]"
                type="text"
                :placeholder="`Option ${index + 1}`"
                class="input mr-2"
              />
              <button
                type="button"
                @click="removePollOption(index)"
                class="btn btn-danger py-2"
                :disabled="form.meta[field].length <= 2"
              >
                <Icon icon="bx:trash" class="text-lg" />
              </button>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="addPollOption" class="btn btn-primary py-1">
                <Icon icon="bx:plus" />
                <span>{{ trans('Option') }}</span>
              </button>
            </div>
          </div>

          <div v-else-if="getFieldType(field, value) === 'input'">
            <template v-if="field !== `${activeTab}Url` && field !== `${activeTab}_name`">
              <InputField
                v-model="form.meta[field]"
                :label="field"
                :placeholder="`Enter ${field}`"
              />
            </template>
          </div>

          <div v-else>
            <label class="label mb-1 block capitalize">{{ field }}</label>
            <div v-if="field === 'text'">
              <button
                v-for="btn in textTransformButtons"
                :key="btn.label"
                type="button"
                class="btn btn-outline-primary btn-xs mr-2"
                @click="transformText(field, btn.symbol)"
              >
                {{ btn.label }}
              </button>
            </div>
            <textarea
              v-model="form.meta[field]"
              :name="`meta.${field}`"
              class="textarea mt-2"
            ></textarea>
            <ShortCodes v-model="form.meta[field]" />
          </div>
        </template>
        <div class="flex justify-end">
          <SpinnerBtn
            btn-text="Submit"
            type="submit"
            class="btn btn-primary mt-4"
            :processing="form.processing"
          />
        </div>
      </form>
    </div>

    <!-- Preview section -->
    <div class="w-full md:col-span-3">
      <div
        class="whatsapp-chat-body relative h-[35rem] rounded-xl border-2 border-dark-400 outline outline-4 outline-dark-500 dark:border-dark-800 dark:outline-dark-950"
      >
        <div
          class="absolute bottom-3 left-4 w-10/12 rounded-lg bg-white p-1 dark:bg-dark-700 xl:w-8/12"
        >
          <TemplatePreview :template="form" />
        </div>
      </div>
    </div>
  </div>
</template>
