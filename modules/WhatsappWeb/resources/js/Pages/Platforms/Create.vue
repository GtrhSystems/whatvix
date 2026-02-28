<script setup>
import { useForm } from '@inertiajs/vue3'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import InputField from '@/Components/Forms/InputField.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['platforms'])

const form = useForm({
  name: '',
  phone_number: null
})

const handleFormSubmit = () => {
  form.post(route('user.whatsapp-web.platforms.store'))
}
</script>

<template>
  <div class="relative mx-auto mb-6 flex max-w-2xl justify-between overflow-hidden px-4">
    <div class="flex min-w-0 flex-col items-center gap-1">
      <span
        class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-700 text-white"
      >
        1
      </span>
      <span
        class="max-w-full overflow-hidden text-ellipsis whitespace-nowrap text-center text-xs md:max-w-[150px]"
      >
        {{ trans('Create Device') }}
      </span>
    </div>

    <div class="flex min-w-0 flex-col items-center gap-1">
      <span
        class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-primary-500 bg-dark-50 dark:bg-dark-800"
      >
        2
      </span>
      <span
        class="max-w-full overflow-hidden text-ellipsis whitespace-nowrap text-center text-xs md:max-w-[150px]"
      >
        {{ trans('Connect Device') }}
      </span>
    </div>

    <hr
      class="absolute left-1/2 top-5 z-0 h-1 w-[calc(100%-5rem)] -translate-x-1/2 border-none bg-primary-700 md:block"
    />
  </div>
  <div class="card mx-auto max-w-2xl p-6">
    <p class="mb-3 text-lg font-semibold">{{ trans('Create Device') }}</p>
    <form class="space-y-3" @submit.prevent="handleFormSubmit">
      <InputField
        label="Device Title"
        v-model="form.name"
        placeholder="My Personal Device"
        :error="form.errors.name"
      />

      <InputField
        type="tel"
        label="Whatsapp Number"
        v-model="form.phone_number"
        placeholder="Ex: 8801234567890"
        :error="form.errors.phone_number"
      />
      <p class="text-xs text-red-500">
        {{ trans('Note: Number must be start with country code') }}
      </p>

      <div class="flex justify-end">
        <SpinnerBtn :processing="form.processing" :btn-text="trans('Submit')" />
      </div>
    </form>
  </div>
</template>
