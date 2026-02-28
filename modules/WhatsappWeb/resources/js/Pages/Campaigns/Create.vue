<script setup>
import { computed } from 'vue'

import InputField from '@/Components/Forms/InputField.vue'
import ShortCodes from '@/Components/Forms/ShortCodes.vue'
import { useForm } from '@inertiajs/vue3'
import TextareaField from '@/Components/Forms/TextareaField.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import RangeSlider from '@/Components/RangeSlider.vue'
import TemplatePreview from '@whatsappWeb/Pages/Templates/Partials/TemplatePreview.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['platforms', 'templates', 'groups', 'time_zone_list'])

const form = useForm({
  module: 'whatsapp-web',
  title: '',
  platform_id: '',
  group_id: '',
  template_id: '',
  message_type: 'text',
  message_template: '',
  is_scheduled: false,
  schedule_at: null,
  timezone: 'Asia/Dhaka',
  delay_between: [1, 100]
})

const submitForm = () => {
  form.post(route('user.whatsapp-web.campaigns.store'))
}

const selectedTemplate = computed(() => {
  return props.templates.find((template) => template.id == form.template_id)
})
</script>

<template>
  <div class="mt-4 grid grid-cols-1 place-items-start gap-6 sm:grid-cols-12">
    <div class="card card-body sm:col-span-9">
      <form @submit.prevent="submitForm" class="space-y-4 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
          {{ trans('Create Campaign') }}
        </h2>

        <div class="mb-5">
          <InputField
            v-model="form.title"
            label="Campaign Title"
            class="w-full"
            :placeholder="trans('Enter a descriptive campaign name')"
          />
        </div>

        <!-- Core Settings Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <!-- Platform Selection -->
          <div>
            <label class="label mb-1 flex items-center">
              <span>{{ trans('Platform') }}</span>
              <a
                :href="route('user.whatsapp-web.platforms.index')"
                class="ml-2 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
              >
                <span class="inline-flex items-center">
                  <span>{{ trans('Add New') }}</span>
                  <Icon class="ml-1 h-3 w-3" icon="bx:plus" />
                </span>
              </a>
            </label>
            <select v-model="form.platform_id" class="select 0">
              <option value="">{{ trans('Select Platform') }}</option>
              <option v-for="platform in platforms" :key="platform.id" :value="platform.id">
                {{ platform.name }}
              </option>
            </select>
          </div>

          <!-- Group Selection -->
          <div>
            <label class="label mb-1 flex items-center">
              <span>{{ trans('Group') }}</span>
              <a
                :href="route('user.whatsapp-web.groups.index')"
                class="ml-2 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
              >
                <span class="inline-flex items-center">
                  <span>{{ trans('Add New') }}</span>
                  <Icon class="ml-1 h-3 w-3" icon="bx:plus" />
                </span>
              </a>
            </label>
            <select v-model="form.group_id" class="select">
              <option value="">{{ trans('Select Group') }}</option>
              <option v-for="group in groups" :key="group.id" :value="group.id">
                {{ group.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Message Configuration Card -->
        <div class="mt-6 rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <p class="mb-3 text-lg font-medium text-gray-700 dark:text-gray-300">
            {{ trans('Message Configuration') }}
          </p>

          <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <!-- Message Type -->
            <div>
              <label class="label mb-1">{{ trans('Message Type') }}</label>
              <select v-model="form.message_type" class="select">
                <option value="text">{{ trans('Text') }}</option>
                <option value="template">{{ trans('Template') }}</option>
              </select>
            </div>

            <!-- Template Selection (conditional) -->
            <div v-if="form.message_type === 'template'">
              <label class="label mb-1">{{ trans('Template') }}</label>
              <select v-model="form.template_id" class="select">
                <option value="" disabled selected>{{ trans('Select Template') }}</option>
                <option v-for="template in templates" :key="template.id" :value="template.id">
                  {{ template.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Message Delay Slider -->
          <div class="col-span-full">
            <label for="device_rotation_duration" class="label mb-1">
              {{ trans('Message Delay (in seconds)') }}
            </label>
            <RangeSlider class="px-1" v-model="form.delay_between" :step="1" />
          </div>

          <template v-if="form.message_type === 'text'">
            <TextareaField
              v-model="form.message_template"
              label="Message Template"
              placeholder="Enter the campaign message template"
              :attrs="{ rows: 5 }"
            />
            <ShortCodes v-model="form.message_template" />
          </template>
        </div>

        <!-- Scheduling Options -->

        <div
          class="flex flex-col rounded-md border border-gray-200 p-4 dark:border-gray-700 md:flex-row md:items-center md:justify-between"
        >
          <div class="mb-4 flex items-center md:mb-0">
            <label for="toggle-checkbox_1" class="toggle">
              <input
                class="toggle-input peer sr-only"
                v-model="form.is_scheduled"
                id="toggle-checkbox_1"
                type="checkbox"
                checked=""
              />
              <div class="toggle-body"></div>
              <span class="label">{{ trans('Set Schedule') }}</span>
            </label>
          </div>

          <div
            v-if="form.is_scheduled"
            class="flex flex-col items-center space-y-3 md:flex-row md:space-x-3 md:space-y-0"
          >
            <select v-model="form.timezone" class="select select-md">
              <option disabled>{{ trans('Select Timezone') }}</option>
              <option v-for="timezone in time_zone_list" :key="timezone" :value="timezone">
                {{ timezone }}
              </option>
            </select>
            <input
              class="input input-md"
              v-model="form.schedule_at"
              type="datetime-local"
              :min="new Date()"
            />
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            {{ trans('Cancel') }}
          </button>
          <SpinnerBtn
            :btn-text="trans(form.is_scheduled ? 'Schedule Campaign' : 'Send Now')"
            type="submit"
            class="btn btn-primary"
            :processing="form.processing"
            :icon="form.is_scheduled ? 'fe:clock' : 'bx:send'"
          />
        </div>
      </form>
    </div>

    <div class="w-full sm:col-span-3">
      <div
        class="whatsapp-chat-body relative h-[35rem] rounded-xl border-2 border-dark-400 outline outline-4 outline-dark-500 dark:border-dark-800 dark:outline-dark-950"
      >
        <div
          class="absolute bottom-3 left-4 w-10/12 rounded-lg bg-white p-1 px-1 dark:bg-dark-700 xl:w-8/12"
        >
          <TemplatePreview
            v-if="form.message_type == 'template' && selectedTemplate"
            :template="selectedTemplate"
          />
          <p
            v-else-if="form.message_type == 'text'"
            class="rounded-lg bg-gray-100 p-2 text-xs leading-4 dark:bg-dark-800"
          >
            {{ form.message_template || trans('The message will appear here') }}
          </p>
          <p v-else class="rounded-lg bg-gray-100 p-2 text-[11px] leading-4 dark:bg-dark-800">
            {{ trans('The message will appear here') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
