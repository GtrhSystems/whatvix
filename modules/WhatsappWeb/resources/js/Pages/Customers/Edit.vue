<script setup>
import { useForm } from '@inertiajs/vue3'
import ImageInput from '@/Components/Forms/ImageInput.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'

import MultiSelect from '@/Components/Forms/MultiSelect.vue'

import InputField from '@/Components/Forms/InputField.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['groups', 'customer', 'groupIds'])

const form = useForm({
  name: props.customer?.name,
  picture: props.customer?.picture,
  group_ids: props.groupIds ?? [],
  phone: props.customer?.uuid,
  _method: 'put'
})

const handleFormSubmit = () => {
  form.post(route('user.whatsapp-web.customers.update', props.customer))
}
</script>
<template>
  <div class="card mx-auto max-w-2xl">
    <div class="card-body">
      <div class="grid grid-cols-1 gap-4">
        <InputField
          label="Name"
          v-model="form.name"
          placeholder="Enter customer full name"
          :validationMessage="form.errors?.name"
        />
        <div class="mb-2">
          <label>{{ trans('Whatsapp Number') }}</label>
          <input v-model="form.phone" class="input" placeholder="ex: 880 1234567890" />
          <p class="mt-1 text-sm">
            {{ trans(' Note:') }}
            <span class="alert-danger mt-1 rounded px-1">
              {{ trans('The number should start with country code') }}</span
            >
          </p>
          <p class="text-red-600">{{ form.errors.phone }}</p>
        </div>

        <MultiSelect
          label="Select groups"
          v-model="form.group_ids"
          placeholder="Select groups"
          :validationMessage="form.errors?.group_ids"
          :options="groups"
          valueProp="value"
        />

        <div class="flex flex-col gap-2">
          <label>{{ trans('Profile Image (Avatar)') }}</label>
          <ImageInput v-model="form.picture" />
        </div>
      </div>

      <div class="mt-6 flex w-full justify-end">
        <SpinnerBtn @click="handleFormSubmit" :processing="form.processing" btn-text="Update" />
      </div>
    </div>
  </div>
</template>
