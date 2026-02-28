<script setup>
import { computed, ref, watch, onMounted } from 'vue'

import axios from 'axios'

import { useForm } from '@inertiajs/vue3'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import BlankLayout from '@/Layouts/BlankLayout.vue'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'

import SelectField from '@/Components/Forms/SelectField.vue'
import ShortCodes from '@whatsapp/Components/ShortCodes.vue'
import TemplatePreview from '@whatsapp/Components/Preview/TemplateMessage.vue'
import InteractiveCreateForm from '@whatsapp/Components/Interactive/CreateForm.vue'
import InteractivePreview from '@whatsapp/Components/Preview/InteractiveMessage.vue'
import TemplateComponent from '@whatsapp/Components/Template/TemplateComponent.vue'
import ValidationErrors from '@/Components/Dashboard/ValidationErrors.vue'

defineOptions({ layout: BlankLayout })

const props = defineProps(['devices', 'autoReply'])

const form = useForm({
  platform_id: '',
  keywords: [],
  match_type: '',
  message_type: 'text',
  message_template: undefined,
  template_id: undefined,
  meta: {},
  status: 'active',
  _method: 'POST'
})

const isEditing = !!props.autoReply?.id

onMounted(() => {
  if (isEditing) {
    form.platform_id = props.autoReply.platform_id
    form.keywords = props.autoReply.keywords ?? []
    form.match_type = props.autoReply.match_type
    form.message_type = props.autoReply.message_type
    form.message_template = props.autoReply.message_template ?? ''
    form.template_id = props.autoReply.template_id
    form.meta = props.autoReply.meta
    form.status = props.autoReply.status
    form._method = 'PUT'

    if (props.autoReply.message_type != 'text') {
      getTemplateList()
    }
  }
})

const templates = ref([])
const getTemplateList = async (setTemplateId) => {
  if (!form.platform_id || form.message_type == 'text') {
    console.log('ignore')
    return
  }

  try {
    const res = await axios.get(
      `/user/whatsapp/get-device-template-list/?platform_id=${form.platform_id}&type=${form.message_type}`
    )
    templates.value = res.data
    if (setTemplateId) {
      form.meta = templates.value.find((t) => t.id == form.template_id)?.meta || []
    }
  } catch (err) {
    console.error(err)
  }
}

const handleFormSubmit = () => {
  let url = route('user.whatsapp.auto-replies.store')

  if (isEditing) {
    url = route('user.whatsapp.auto-replies.update', props.autoReply.id)
  }

  form.post(url)
}
</script>
<template>
  <main class="p-4 sm:p-6">
    <PageHeader />

    <ValidationErrors />

    <form @submit.prevent="handleFormSubmit">
      <div class="mt-8 grid grid-cols-2 place-items-start gap-6 md:grid-cols-12">
        <div class="card col-span-full flex flex-col gap-4 px-4 pb-8 pt-4 md:col-span-4">
          <SelectField
            label="Select A Device"
            v-model="form.platform_id"
            placeholder="SELECT"
            :validationMessage="form.errors?.platform_id"
            :options="devices"
          />

          <MultiSelect
            label="Keywords"
            v-model="form.keywords"
            placeholder="enter keywords"
            :validationMessage="form.errors?.keywords"
            :options="form.keywords"
          />

          <SelectField
            label="Message Type"
            v-model="form.message_type"
            placeholder="SELECT"
            @change="getTemplateList"
            :validationMessage="form.errors?.message_type"
            :options="['text', 'template', 'interactive']"
          />

          <SelectField
            v-if="form.message_type != 'text'"
            label="Select a Template"
            v-model="form.template_id"
            @change="(e) => getTemplateList(e.target.value)"
            placeholder="SELECT"
            :validationMessage="form.errors?.template_id"
            :options="templates"
          />

          <SelectField
            label="Status"
            v-model="form.status"
            placeholder="SELECT"
            :validationMessage="form.errors?.status"
            :options="['active', 'inactive']"
          />
        </div>
        <div class="col-span-full w-full md:col-span-8" v-if="form.meta">
          <div
            v-if="form.message_type != 'text'"
            class="grid grid-cols-2 place-items-start gap-3 lg:grid-cols-3"
          >
            <div class="col-span-full w-full lg:col-span-2">
              <InteractiveCreateForm v-if="form.message_type == 'interactive'" :meta="form.meta" />
              <TemplateComponent
                v-else
                :components="form.meta?.components ?? []"
                :errors="form.errors"
              />
            </div>
            <div class="whatsapp-chat-body col-span-full w-full rounded-md p-6 lg:col-span-1">
              <InteractivePreview
                v-if="form.message_type == 'interactive'"
                :components="form.meta"
              />
              <TemplatePreview v-else :templateData="form.meta?.components ?? []" />
            </div>
          </div>

          <template v-if="form.message_type == 'text'">
            <label for="">{{ trans('Message') }}</label>
            <textarea class="input" v-model="form.message_template" rows="5"></textarea>
            <div v-if="form.errors?.message_template" class="text-red-500">
              {{ form.errors?.message_template }}
            </div>
            <ShortCodes v-model="form.message_template" />
          </template>

          <div class="mt-4 flex justify-end">
            <SpinnerBtn :processing="form.processing" :btn-text="isEditing ? 'Update' : 'Create'" />
          </div>
        </div>
      </div>
    </form>
  </main>
</template>
