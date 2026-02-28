<script setup>
import { computed, onMounted, ref } from 'vue'
import ShortCodes from '@whatsapp/Components/ShortCodes.vue'
import { useForm } from '@inertiajs/vue3'
import BlankLayout from '@/Layouts/BlankLayout.vue'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'

import InputField from '@whatsapp/Components/InputField.vue'
import InteractiveCreateForm from '@whatsapp/Components/Interactive/CreateForm.vue'
import InteractivePreview from '@whatsapp/Components/Preview/InteractiveMessage.vue'
import SelectField from '@/Components/Forms/SelectField.vue'
import TemplatePreview from '@whatsapp/Components/Preview/TemplateMessage.vue'
import TemplateComponent from '@whatsapp/Components/Template/TemplateComponent.vue'
import MessagePreview from '@whatsapp/Components/MessagePreview.vue'

defineOptions({ layout: BlankLayout })
const props = defineProps(['devices', 'groups', 'time_zone_list', 'editCampaign'])

const templates = ref([])

const form = useForm({
  name: '',
  platform_id: '',
  group_id: '',
  interactive_type: '',
  send_type: 'instant',
  message_type: 'text',
  message: '',
  template_id: '',
  meta: [],
  timezone: null,
  schedule_time: null,
  save_as_template: false,
  save_as_draft: true,
  _method: 'POST'
})

const isTemplateType = (message_type) => ['template', 'interactive'].includes(message_type)

onMounted(() => {
  if (props.editCampaign) {
    form.name = props.editCampaign.name
    form.platform_id = props.editCampaign.platform_id
    form.group_id = props.editCampaign.group_id
    form.interactive_type = props.editCampaign.interactive_type
    form.message = props.editCampaign.meta?.text
    form.send_type = props.editCampaign.send_type
    form.message_type = props.editCampaign.message_type
    form.meta = props.editCampaign.meta
    form.template_id = props.editCampaign.template_id
    form.save_as_template = props.editCampaign.save_as_template
    form.save_as_draft = props.editCampaign.save_as_draft
    form._method = 'PUT'

    if (isTemplateType(form.message_type)) {
      getTemplateList(props.editCampaign.template_id)
    }
  }

  let tz = Intl.DateTimeFormat().resolvedOptions().timeZone
  if (tz) {
    form.timezone = tz
    form.schedule_time = `${
      new Date().toISOString().split('T')[0]
    }T${new Date().getHours()}:${new Date().getMinutes()}`
  }
})

const isScheduled = computed(() => form.send_type === 'scheduled')

const setTemplateMeta = () => {
  form.meta = templates.value.find((t) => t.id == form.template_id)?.meta || []
}

const handleFormSubmit = () => {
  if (props.editCampaign) {
    form.post(route('user.whatsapp.campaigns.update', props.editCampaign.id))
  } else {
    form.post('/user/whatsapp/campaigns')
  }
}

const getTemplateList = (setIdAfterGet = '') => {
  if (!form.platform_id || !isTemplateType(form.message_type)) return
  form.template_id = ''
  axios
    .get(
      `/user/whatsapp/get-device-template-list?platform_id=${form.platform_id}&type=${form.message_type}`
    )
    .then((res) => {
      form.template_id = setIdAfterGet
      templates.value = res.data
    })
    .catch((err) => {
      console.error(err)
    })
}
</script>
<template>
  <main class="p-4 sm:p-6">
    <PageHeader />

    <ul v-if="Object.keys(form.errors).length > 0" class="card card-body">
      <li class="text-red-600" v-for="err in form.errors" :key="err">
        {{ err }}
      </li>
    </ul>

    <div class="mt-8 grid grid-cols-2 gap-6 md:grid-cols-12">
      <div
        class="card col-span-full flex flex-col gap-4 px-4 pb-8 pt-4 md:col-span-4 lg:col-span-2"
      >
        <InputField
          label="Name"
          v-model="form.name"
          placeholder="Enter campaign title"
          :validationMessage="form.errors.name"
        />

        <SelectField
          label="Device"
          v-model="form.platform_id"
          placeholder="SELECT"
          :validationMessage="form.errors.platform_id"
          :options="devices"
        />

        <SelectField
          label="Group"
          v-model="form.group_id"
          placeholder="SELECT"
          :validationMessage="form.errors.group_id"
          :options="groups"
        />

        <SelectField
          label="Message Type"
          v-model="form.message_type"
          placeholder="SELECT"
          @change="getTemplateList"
          :validationMessage="form.errors.type"
          :options="['text', 'template', 'interactive']"
        />

        <div v-if="isTemplateType(form.message_type)">
          <SelectField
            label="Template"
            v-model="form.template_id"
            @change="setTemplateMeta"
            placeholder="SELECT"
            :validationMessage="form.errors.template_id"
            :options="templates"
          />
        </div>

        <div v-if="form.message_type == 'interactive'">
          <label for="toggle-checkbox_2" class="toggle">
            <input
              class="toggle-input peer sr-only"
              v-model="form.save_as_template"
              id="toggle-checkbox_2"
              type="checkbox"
              checked=""
            />
            <div class="toggle-body"></div>
            <span class="label">{{ trans('Save As Template') }}</span>
          </label>
        </div>

        <div>
          <label for="toggle-checkbox_0" class="toggle">
            <input
              class="toggle-input peer sr-only"
              v-model="form.send_type"
              id="toggle-checkbox_0"
              true-value="draft"
              false-value="instant"
              type="checkbox"
              checked=""
            />
            <div class="toggle-body"></div>
            <span class="label">{{ trans('Save As Draft') }}</span>
          </label>
        </div>

        <div>
          <label for="toggle-checkbox_1" class="toggle">
            <input
              class="toggle-input peer sr-only"
              v-model="form.send_type"
              true-value="scheduled"
              false-value="instant"
              id="toggle-checkbox_1"
              type="checkbox"
              checked=""
            />
            <div class="toggle-body"></div>
            <span class="label">{{ trans('Set Schedule') }}</span>
          </label>
        </div>

        <template v-if="isScheduled">
          <select v-model="form.timezone" class="select">
            <option disabled>{{ trans('Select Timezone') }}</option>
            <option v-for="(timezone, index) in time_zone_list" :key="index" :value="timezone">
              {{ timezone }}
            </option>
          </select>
          <input
            class="input input-datetime"
            v-model="form.schedule_time"
            type="datetime-local"
            :min="new Date()"
            placeholder="YYYY-MM-DD HH:MM"
          />
        </template>
      </div>

      <div class="col-span-full w-full md:col-span-8 lg:col-span-10">
        <div class="grid grid-cols-2 place-items-start gap-3 lg:grid-cols-3">
          <div class="col-span-full w-full lg:col-span-2">
            <InteractiveCreateForm
              v-if="form.message_type == 'interactive' && form.template_id"
              :errors="form.errors"
              :meta="form.meta"
            />
            <TemplateComponent
              v-else-if="form.message_type == 'template'"
              :components="form.meta.components"
              :errors="form.errors"
            />

            <div class="col-span-8" v-if="form.message_type == 'text'">
              <textarea
                class="textarea"
                v-model="form.message"
                placeholder="enter message"
              ></textarea>
              <ShortCodes v-model="form.message" />
              <p class="text-small text-danger-500">
                {{ form.errors?.message }}
              </p>
            </div>
          </div>

          <div class="whatsapp-chat-body col-span-full w-full rounded-md p-6 lg:col-span-1">
            <InteractivePreview v-if="form.message_type == 'interactive'" :components="form.meta" />
            <TemplatePreview
              v-else-if="form.message_type == 'template'"
              :templateData="form.meta.components"
            />
            <MessagePreview
              v-else-if="form.message_type == 'text'"
              :message="{ type: 'text', body: form.message }"
            />
          </div>
        </div>
        <div class="mt-2 flex justify-end">
          <button disabled v-if="form.processing" class="btn btn-primary">
            {{ trans('Sending') }}
            <div role="status">
              <svg
                aria-hidden="true"
                class="h-4 w-4 animate-spin fill-blue-600 text-gray-200 dark:text-gray-600"
                viewBox="0 0 100 101"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                  fill="currentColor"
                />
                <path
                  d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                  fill="currentFill"
                />
              </svg>
              <span class="sr-only">{{ trans('Loading...') }}</span>
            </div>
          </button>
          <button v-else @click="handleFormSubmit" class="btn btn-primary capitalize">
            {{ editCampaign ? 'Update' : form.send_type == 'instant' ? 'Send' : form.send_type }}
            {{ trans('Campaign') }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>
