<script setup>
import { computed } from 'vue'

import { useForm } from '@inertiajs/vue3'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'

import ShortCodes from '@/Components/Forms/ShortCodes.vue'
import SelectField from '@/Components/Forms/SelectField.vue'
import TextareaField from '@/Components/Forms/TextareaField.vue'
import TemplatePreview from '@whatsappWeb/Pages/Templates/Partials/TemplatePreview.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['platforms', 'sort_codes', 'templates', 'autoReply'])

const isEdit = !!props.autoReply

const form = useForm({
  platform_id: '',
  keywords: [],
  message_type: 'text',
  match_type: '',
  template_id: '',
  meta: {},
  message_template: '',
  status: 'active',
  _method: 'post'
})

if (isEdit) {
  form.platform_id = props.autoReply.platform_id
  form.keywords = props.autoReply.keywords
  form.message_type = props.autoReply.message_type
  form.match_type = props.autoReply.match_type
  form.template_id = props.autoReply.template_id
  form.meta = props.autoReply.meta
  form.message_template = props.autoReply.message_template
  form.status = props.autoReply.status
  form._method = 'put'
}

const handleFormSubmit = () => {
  form.post(
    isEdit
      ? route('user.whatsapp-web.auto-replies.update', props.autoReply)
      : route('user.whatsapp-web.auto-replies.store'),
    {
      preserveScroll: true,
      onSuccess: () => form.reset()
    }
  )
}

const selectedTemplate = computed(() => {
  return props.templates.find((template) => template.id == form.template_id)
})
</script>
<template>
  <main class="p-4 sm:p-6">
    <PageHeader />
    <div class="mt-4 grid grid-cols-1 place-items-start gap-4 md:grid-cols-12">
      <div class="card card-body md:col-span-9">
        <form @submit.prevent="handleFormSubmit">
          <div class="grid grid-cols-2 gap-2">
            <SelectField
              label="Device"
              v-model="form.platform_id"
              placeholder="SELECT"
              :validationMessage="form.errors?.platform_id"
              :options="platforms"
            />

            <SelectField
              label="Message Type"
              v-model="form.message_type"
              placeholder="SELECT"
              :validationMessage="form.errors?.message_type"
              :options="['text', 'template']"
            />

            <SelectField
              v-if="form.message_type === 'template'"
              class="col-span-1"
              label="Select a Template"
              v-model="form.template_id"
              placeholder="SELECT"
              :validationMessage="form.errors?.template_id"
              :options="templates"
            />

            <MultiSelect
              :class="form.message_type === 'text' ? 'col-span-full' : 'col-span-1'"
              label="Keywords"
              v-model="form.keywords"
              placeholder="enter keywords"
              :validationMessage="form.errors?.keywords"
              :options="[]"
            />

            <div class="col-span-3" v-if="form.message_type === 'text'">
              <TextareaField
                label="Message"
                placeholder="Enter the message template"
                v-model="form.message_template"
                :validationMessage="form.errors?.message_template"
                :attrs="{ rows: 5 }"
              />
              <ShortCodes v-model="form.message_template" />
            </div>
          </div>
          <div class="mt-2 flex items-end justify-between">
            <SelectField
              class="max-w-xs"
              label="Status"
              v-model="form.status"
              placeholder="SELECT"
              :validationMessage="form.errors?.status"
              :options="['active', 'inactive']"
            />
            <SpinnerBtn :processing="form.processing" :btn-text="isEdit ? 'Update' : 'Create'" />
          </div>
        </form>
      </div>

      <div class="w-full md:col-span-3">
        <div
          class="whatsapp-chat-body relative h-[35rem] rounded-xl border-2 border-dark-400 outline outline-4 outline-dark-500 dark:border-dark-800 dark:outline-dark-950"
        >
          <div
            class="absolute bottom-3 left-4 w-10/12 rounded-lg bg-white p-1 px-2 dark:bg-dark-700 xl:w-8/12"
          >
            <TemplatePreview
              v-if="form.message_type == 'template' && selectedTemplate"
              :template="selectedTemplate"
            />

            <div v-else-if="form.message_type == 'text' && !selectedTemplate">
              <p class="rounded-lg bg-gray-100 p-2 text-[11px] leading-4 dark:bg-dark-800">
                {{ form.message_template || trans('The message will appear here') }}
              </p>
              <p class="text-end text-[10px] text-gray-400">
                {{ new Date().toLocaleTimeString() }}
              </p>
            </div>
            <p v-else>{{ trans('The message will appear here') }}</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
