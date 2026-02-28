<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'
import InputField from '@/Components/Forms/InputField.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['groups'])

const form = useForm({
  name: '',
  picture: '',
  group_ids: [],
  phone: ''
})

const handleFormSubmit = () => {
  form.post(route('user.whatsapp-web.customers.store'))
}
</script>
<template>
  <div class="card mx-auto max-w-2xl">
    <div class="card-body">
      <div class="space-y-2">
        <InputField
          label="Name"
          v-model="form.name"
          placeholder="Ex: John Doe"
          :validationMessage="form.errors?.name"
        />
        <div class="mb-2">
          <InputField
            type="tel"
            label="Phone"
            v-model="form.phone"
            placeholder="ex: 880 1234567890"
            :validationMessage="form.errors?.phone"
          />
          <p class="mt-1 text-xs text-red-500">
            {{ trans('Note') }}:
            <span class="mt-1 rounded px-1">
              {{ trans('The number should start with country code') }}</span
            >
          </p>
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
          <label class="label">{{ trans('Profile Image (Avatar)') }}</label>
          <input
            type="file"
            class="input"
            @change="(e) => (form.picture = e.target.files[0])"
            accept="image/png"
          />
          <p class="text-red-600">{{ form.errors.picture }}</p>
        </div>
      </div>
      <div class="mt-2 text-end">
        <SpinnerBtn @click="handleFormSubmit" :processing="form.processing" btn-text="Create" />
      </div>
    </div>
  </div>
</template>
