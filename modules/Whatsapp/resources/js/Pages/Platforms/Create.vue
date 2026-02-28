<script setup>
import { ref } from 'vue'

import axios from 'axios'

import sharedComposable from '@/Composables/sharedComposable'
import toastr from '@/Composables/toastComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import InputField from '../../Components/InputField.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: UserLayout })
const props = defineProps(['devices', 'totalCounter'])

const { copyToClipboard } = sharedComposable()
const form = ref({
  name: null,
  provider: 'meta',
  phone_number_id: null,
  business_account_id: null,
  access_token: null
})

const currentStep = ref(1)
const validationMessages = ref(null)
var device = ref(null)
const isLoading = ref(false)
const handleFormSubmit = () => {
  isLoading.value = true
  axios
    .post(route('user.whatsapp.platforms.store'), form.value)
    .then((res) => {
      device = res.data.platform
      currentStep.value = 3
      toastr.success(res.data?.message ?? 'Device created successfully')
    })
    .catch((error) => {
      if (error.response.status == 422) {
        validationMessages.value = error.response.data.errors
        if (validationMessages.value?.name) {
          currentStep.value = 1
        }
      }
      if (error.response.status == 500) {
        toastr.danger(error.response.data?.message ?? 'Server Error')
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

function nextStep() {
  if (currentStep.value < 2) {
    currentStep.value++
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}
</script>

<template>
  <div class="card mx-auto max-w-2xl p-10">
    <div>
      <ul class="mx-auto flex max-w-md items-center">
        <li class="z-10 -mr-0.5 flex flex-col items-center gap-y-1">
          <span
            class="flex h-7 w-7 items-center justify-center rounded-full"
            :class="
              currentStep == 1 || currentStep > 1
                ? 'bg-primary-500 text-white'
                : 'border border-gray-500'
            "
          >
            <template v-if="currentStep == 1"> 1 </template>
            <Icon v-if="currentStep > 1" icon="bx:check" />
          </span>
        </li>
        <hr
          class="h-2 w-48 border-0"
          :class="currentStep > 1 ? 'bg-primary-500' : 'bg-dark-400 dark:bg-dark-700'"
        />
        <li class="z-10 -mx-0.5 flex flex-col items-center gap-y-1">
          <span
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
            :class="
              currentStep == 2 || currentStep > 1
                ? 'bg-primary-500 text-white'
                : 'border border-gray-500'
            "
          >
            <Icon v-if="currentStep > 2" icon="bx:check" />
            <template v-else> 2 </template>
          </span>
        </li>
        <hr
          class="h-2 w-48 border-0"
          :class="currentStep > 2 ? 'bg-primary-500' : 'bg-dark-400 dark:bg-dark-700'"
        />
        <li class="z-10 -ml-0.5 flex flex-col items-center gap-y-1">
          <span
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
            :class="
              currentStep == 3 || currentStep > 2
                ? 'bg-primary-500 text-white'
                : 'border border-gray-500'
            "
          >
            3
          </span>
        </li>
      </ul>
      <ul class="mx-auto mt-1 flex max-w-md items-center justify-between text-sm">
        <li class="flex translate-x-[-22px] flex-col items-center gap-y-1">
          {{ trans('Device Name') }}
        </li>

        <li class="flex translate-x-[-22px] flex-col items-center gap-y-1">
          {{ trans('Information') }}
        </li>

        <li class="flex translate-x-[4px] flex-col items-center gap-y-1">
          {{ trans('Verify') }}
        </li>
      </ul>
    </div>
    <form class="step-form" @submit.prevent="handleFormSubmit">
      <InputField
        class="mt-8"
        v-show="currentStep === 1"
        label="Title"
        v-model="form.name"
        placeholder="Enter device name"
        :validationMessage="validationMessages?.name"
      />

      <div class="mt-4" v-show="currentStep === 1">
        <label class="label mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Provider</label>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="form.provider" value="meta" class="radio radio-primary" />
            <span>Meta (Cloud API)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="form.provider" value="ycloud" class="radio radio-primary" />
            <span>YCloud</span>
          </label>
        </div>
      </div>

      <div class="mt-8 flex flex-col gap-4" v-show="currentStep === 2">
        <div class="text-[12px] font-normal" v-if="form.provider === 'meta'">
          <span>
            {{
              trans('At first you need to create an app in Meta Developer in you Facebook account.')
            }}
            <ul class="my-2">
              <li>
                {{
                  trans('1. Create a Developer account and a new Facebook app as described here')
                }}
                (<a
                  target="_blank"
                  href="https://developers.facebook.com/apps"
                  class="cursor-pointer text-primary-600 hover:text-primary-900"
                  >{{ trans('Click') }}</a
                >)
              </li>
              <li>
                {{
                  trans(`2. Once you have your Facebook app created, in the dashboard of the app, locate the
                Whatsapp product Setup.`)
                }}
              </li>
              <li>
                {{
                  trans(
                    `3. Then go to WhatsApp Api Setup (Quickstart > API Setup) and you will find`
                  )
                }}
                (<strong>{{ trans(`access token`) }} </strong>) (<strong>{{
                  trans(`Phone number ID`)
                }}</strong
                >) (<strong>{{ trans('WhatsApp Business Account ID') }}</strong
                >)
              </li>
            </ul>
          </span>
        </div>
        <div class="text-[12px] font-normal" v-else>
          <span>
            {{ trans('Configure YCloud Integration:') }}
            <ul class="my-2">
              <li>
                {{ trans('1. Get your API Key from YCloud Dashboard.') }}
              </li>
              <li>
                {{ trans('2. Enter your Phone Number ID (Sender ID) and WABA ID.') }}
              </li>
            </ul>
          </span>
        </div>

        <InputField
          :label="form.provider === 'ycloud' ? 'API Key' : 'Access Token'"
          v-model="form.access_token"
          :placeholder="form.provider === 'ycloud' ? 'Enter YCloud API Key' : 'Enter access token'"
          :validationMessage="validationMessages?.access_token?.[0]"
        />
        <InputField
          :label="form.provider === 'ycloud' ? 'Sender Phone Number' : 'Phone Number Id'"
          v-model="form.phone_number_id"
          :placeholder="form.provider === 'ycloud' ? 'Enter sender phone number (e.g. 15551234567)' : 'Enter phone number id'"
          :validationMessage="validationMessages?.phone_number_id?.[0]"
        />
        <InputField
          :label="form.provider === 'ycloud' ? 'WABA ID (WhatsApp Business Account ID)' : 'Business Account Id'"
          v-model="form.business_account_id"
          :placeholder="form.provider === 'ycloud' ? 'Enter WABA ID' : 'Enter business id'"
          :validationMessage="validationMessages?.business_account_id?.[0]"
        />
      </div>

      <div class="mt-8 flex flex-col gap-4" v-show="currentStep === 3">
        <div class="text-[12px] font-normal">
          <span>
            {{
              trans(`Congratulation. Webhook is successfully created. You need to add this webhook url into
            your app configuration webhook settings.`)
            }}
            <ul class="text-bold my-2">
              <li>
                {{ trans(`1. Go Meta Developer where you already created an app`) }} (<a
                  target="_blank"
                  id="goto_webhook_setting"
                  class="cursor-pointer text-primary-600 hover:text-primary-900"
                  >{{ trans(`Click`) }}</a
                >)
              </li>
              <li>{{ trans(`2. In the Dashboard you need to add a Product webhooks.`) }}</li>
              <li>
                {{ trans(`3. Then go to WhatsApp`) }} &gt;
                {{ trans(`Configuration and enter the following info`) }}
              </li>
            </ul>
          </span>
        </div>
        <div class="w-full cursor-pointer">
          <label for="" class="label mb-1">{{ trans('Callback Url') }}</label>
          <input
            @click="copyToClipboard(device?.webhook_url || 'url')"
            id="callback-url"
            class="input w-full cursor-pointer !text-gray-800 dark:!text-white"
            disabled
            :value="device?.webhook_url"
          />
        </div>
        <div class="w-full cursor-pointer">
          <label for="" class="label mb-1">{{ trans('Verify Token') }}</label>
          <input
            @click="copyToClipboard(device?.uuid || 'token')"
            id="token"
            class="input w-full cursor-pointer !text-gray-800 dark:!text-white"
            disabled
            :value="device?.uuid"
          />
        </div>

        <Link :href="route('user.whatsapp.platforms.index')" class="btn btn-primary w-full">
          {{ trans('Done') }}
        </Link>
      </div>

      <div v-if="currentStep !== 3" class="mt-8 flex w-full justify-between">
        <button
          type="button"
          :disabled="currentStep === 1"
          @click="prevStep"
          class="btn btn-secondary"
        >
          {{ trans('Previous') }}
        </button>
        <SpinnerBtn
          @click="nextStep"
          :type="currentStep > 1 ? 'submit' : 'button'"
          :processing="isLoading"
          :btn-text="currentStep > 1 ? 'Save' : 'Next'"
        />
      </div>
    </form>
  </div>
</template>
