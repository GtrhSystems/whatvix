<script setup>
import { computed, onMounted, ref, watch } from 'vue'

import { useForm } from '@inertiajs/vue3'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import BlankLayout from '@/Layouts/BlankLayout.vue'

import InputField from '@/Components/Forms/InputField.vue'
import SelectField from '@/Components/Forms/SelectField.vue'

defineOptions({ layout: BlankLayout })

const props = defineProps(['platforms', 'autoReply', 'sort_codes'])

const form = useForm({
  ...props.autoReply
})

const component = computed(() => templates.value.find((c) => c?.id == form.template_id)?.data)
const getSecretValues = (secrets) => {
  form.meta = secrets
}

onMounted(() => {
  if (form.platform_id) {
    getTemplateList(form.platform_id)
  }
})

watch(
  () => form.platform_id,
  function (newValue, oldValue) {
    if (newValue) {
      getTemplateList(newValue)
    }
  }
)

const templates = ref([])
const getTemplateList = (deviceId) => {
  axios
    .get(`/user/whatsapp/get-device-template-list/?platform_id=${deviceId}`)
    .then((res) => {
      templates.value = res.data
    })
    .catch((err) => {
      console.error(err)
    })
}

const handleFormSubmit = () => {
  form.put(route('user.whatsapp-web.auto-replies.update', props.autoReply))
}

const interactiveTypes = [
  'reply_button',
  'cta_button',
  'list',
  'product',
  'product_list',
  'catalog_message'
]

const setShortCode = (code) => {
  form.message_template = `${form.message_template}${code}`
}
</script>
<template>
  <main class="p-4 sm:p-6">
    <PageHeader />
    <form @submit.prevent="handleFormSubmit">
      <div class="mt-8 grid grid-cols-2 place-items-start gap-6 md:grid-cols-12">
        <div
          class="card col-span-full flex flex-col gap-4 px-4 pb-8 pt-4 md:col-span-4 lg:col-span-2"
        >
          <SelectField
            label="Select A Device"
            v-model="form.platform_id"
            placeholder="SELECT"
            :validationMessage="form.errors?.platform_id"
            :options="platforms"
          />
          <MultiSelect
            label="Keywords"
            v-model="form.keywords"
            placeholder="enter keywords"
            :validationMessage="form.errors?.keywords"
            :options="[]"
          />
          <SelectField
            label="Select Type"
            v-model="form.message_type"
            placeholder="SELECT"
            :validationMessage="form.errors?.message_type"
            :options="['text']"
          />

          <SelectField
            label="Status"
            v-model="form.status"
            placeholder="SELECT"
            :validationMessage="form.errors?.status"
            :options="['active', 'inactive']"
          />
        </div>
        <div class="col-span-full w-full md:col-span-8 lg:col-span-10">
          <InputField
            v-if="form.message_type === 'text'"
            label="Message"
            v-model="form.message_template"
            placeholder="ex: Winter sale campaign "
            :validationMessage="form.errors?.message_template"
          />
          <div class="mt-3 flex flex-row items-center gap-3">
            <p>{{ trans('Short codes') }}:</p>
            <div class="flex flex-row items-center gap-3 md:gap-4">
              <template v-for="(code, index) in sort_codes" :key="index">
                <button
                  type="button"
                  class="btn short_code_button bg-slate-300 text-xs text-gray-700 hover:bg-opacity-60 dark:bg-dark-700 dark:text-white dark:hover:bg-opacity-60"
                  @click="setShortCode(code)"
                >
                  {{ code }}
                </button>
              </template>
            </div>
          </div>
          <div class="mt-6 flex w-full justify-end">
            <SpinnerBtn :processing="form.processing" btn-text="Save Changes" />
          </div>
        </div>
      </div>
    </form>
  </main>
</template>
