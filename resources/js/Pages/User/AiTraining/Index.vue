<script setup>
import { ref } from 'vue'

import FloatingDropdown from '@/Components/Dashboard/FloatingDropdown.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

defineOptions({ layout: UserLayout })
const { trim, deleteRow } = sharedComposable()

const props = defineProps([
  'providerConfigSchema',
  'providerConfig',
  'aiTrainingCredentials',
  'aiModules',
  'demoDatasets'
])
const formActiveProvider = ref(null)
const modalStore = useModalStore()
const form = useForm({
  ...props.providerConfigSchema
})
const importDatasetFrom = useForm({
  provider: '',
  dataset: '',
  title: '',
  file_type: 'json'
})
const submit = () => {
  form.post(route('user.ai-training-credential.store'))
}
const findCredential = (provider) => {
  return props.aiTrainingCredentials.find((credential) => credential.provider === provider)
}

const importDataset = () => {
  importDatasetFrom.post(route('user.ai-training.import-dataset'), {
    onSuccess: () => {
      importDatasetFrom.reset()
      modalStore.close('importDataset')
    }
  })
}
</script>

<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <template v-for="(provider, key) in providerConfig" :key="key">
      <div class="card card-body relative">
        <div class="flex items-center justify-between">
          <p class="text-lg capitalize">{{ provider?.info.name }}</p>
          <FloatingDropdown
            btn-type="icon"
            icon-name="bx:dots-vertical-rounded"
            btn-class="p-0"
            position="left"
          >
            <ul class="dropdown-list">
              <li class="dropdown-list-item">
                <button
                  @click="
                    () => {
                      formActiveProvider = key
                      modalStore.open('aiCredentials')
                    }
                  "
                  class="dropdown-link gap-4"
                >
                  <Icon icon="bx:key" />
                  {{ trans('Ai Credentials') }}
                </button>
              </li>
              <li class="dropdown-list-item">
                <Link :href="route('user.ai-training.show', key)" class="dropdown-link gap-4">
                  <Icon icon="bx:align-left" />

                  {{ trans('Logs') }}
                </Link>
              </li>
              <template v-for="item in aiModules[key].dropdown_items" :key="item">
                <li class="dropdown-list-item">
                  <a class="dropdown-link gap-4" :href="item.url">
                    <Icon :icon="item.icon" />

                    {{ item.text }}
                  </a>
                </li>
              </template>
            </ul>
          </FloatingDropdown>
        </div>
        <div class="flex h-32 items-center justify-center pb-5">
          <Icon :icon="provider?.info.icon" class="text-6xl" />
        </div>
      </div>
    </template>
  </div>
  <NoDataFound v-if="Object.keys(providerConfig)?.length < 1" />

  <Modal
    @close="formActiveProvider = null"
    state="aiCredentials"
    modal-type="sidebar"
    :header-state="true"
    :header-title="trans('AI Credentials Configuration')"
    modal-size="w-3/12"
  >
    <div class="styled-scrollbar max-h-[calc(100vh-120px)] overflow-y-auto pb-10">
      <template v-if="formActiveProvider">
        <label class="label mb-1 mt-4 text-lg font-bold capitalize">{{ formActiveProvider }}</label>
        <template v-if="form[formActiveProvider]">
          <div v-for="(value, key) in form[formActiveProvider]" :key="key">
            <label class="label mb-1 capitalize" :for="key">{{ trim(key) }}</label>
            <select
              class="select"
              v-if="key === 'model' && providerConfig[formActiveProvider]?.info?.supported_models"
              v-model="form[formActiveProvider][key]"
            >
              <option value="null" selected>{{ trans('Select Model') }}</option>
              <option
                v-for="model in providerConfig[formActiveProvider].info.supported_models"
                :key="model"
                :value="model"
              >
                {{ model }}
              </option>
            </select>
            <input
              v-else
              class="input"
              :id="key"
              v-model="form[formActiveProvider][key]"
              :disabled="['redirect', 'base_url', 'refresh_token'].includes(key)"
            />
          </div>
        </template>
        <small class="block">* {{ trans('Please! provide correct credentials.') }}</small>
        <small>* {{ trans('Check your credentials before submit.') }}</small>
      </template>
    </div>

    <div class="flex justify-end gap-3">
      <button
        class="btn btn-danger max-w-max"
        @click="deleteRow(route('user.ai-training-credential.destroy', formActiveProvider))"
      >
        <Icon icon="bx:trash" />
        {{ trans('Remove') }}
      </button>
      <SpinnerBtn
        :processing="form.processing"
        @click="submit"
        :btn-text="trans('Save')"
        icon="bx:save"
        class="w-full"
      />
    </div>
  </Modal>

  <Modal state="importDatasetModal" :header-state="true" :header-title="trans('Import Dataset')">
    <form @submit.prevent="importDataset" class="space-y-2">
      <div>
        <label class="label mb-1">{{ trans('Title') }} </label>
        <input type="text" v-model="importDatasetFrom.title" class="input" />
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="label mb-1">{{ trans('Select Provider') }}</label>
          <select class="select capitalize" v-model="importDatasetFrom.provider">
            <option value="">{{ trans('Select Provider') }}</option>
            <option
              v-for="provider in Object.keys(providerConfig)"
              :key="provider"
              :value="provider"
            >
              {{ provider }}
            </option>
          </select>
        </div>
        <div>
          <label class="label mb-1">{{ trans('Select File Format') }}</label>
          <select class="select uppercase" v-model="importDatasetFrom.file_type">
            <option v-for="file_type in ['csv', 'json']" :key="file_type" :value="file_type">
              {{ file_type }}
            </option>
          </select>
        </div>
      </div>
      <div>
        <label class="label mb-1">{{ trans('Select Dataset') }} </label>
        <input
          type="file"
          accept=".json,.csv,.xlsx"
          @change="(e) => (importDatasetFrom.dataset = e.target.files[0])"
          class="input"
        />
      </div>
      <div class="mt-2 flex items-center justify-between">
        <a
          :href="demoDatasets[importDatasetFrom.file_type]"
          class="text-sm text-primary-500 underline"
          download
          target="_blank"
          >{{ trans('Download Demo Dataset') }}</a
        >
        <SpinnerBtn :processing="importDatasetFrom.processing" />
      </div>
    </form>
  </Modal>
</template>
