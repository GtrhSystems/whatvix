<script setup>
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import InputField from '@/Components/Forms/InputField.vue'
import AssetInput from '@/Components/Forms/AssetInput.vue'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['groups', 'dialCodes'])

const form = useForm({
  name: '',
  picture: '',
  group_ids: [],
  dial_code: '+880',
  phone: ''
})

const handleFormSubmit = () => {
  form.post(route('user.whatsapp.customers.store'))
}
</script>
<template>
  <div class="card card-body mx-auto max-w-2xl">
    <div class="space-y-2">
      <InputField
        label="Name"
        v-model="form.name"
        placeholder="Enter customer full name"
        :validationMessage="form.errors?.name"
      />

      <div class="mb-2 grid grid-cols-3 place-items-end gap-3">
        <MultiSelect
          mode="single"
          :close-on-select="false"
          :searchable="true"
          label="Whatsapp Number"
          input-label="name"
          v-model="form.dial_code"
          valueProp="id"
          placeholder="Dial Code"
          :validationMessage="form.errors?.dial_code"
          :options="dialCodes"
        />

        <InputField
          classes="col-span-2 "
          type="tel"
          v-model="form.phone"
          placeholder="enter customer number (without country code)"
          :validationMessage="form.errors?.phone"
        />
      </div>

      <MultiSelect
        label="Groups"
        v-model="form.group_ids"
        placeholder="Select groups"
        :validationMessage="form.errors?.group_ids"
        :options="groups"
        valueProp="value"
      />

      <div class="flex flex-col gap-2">
        <AssetInput label="Profile Image (Avatar)" v-model="form.picture" />
      </div>
    </div>
    <div class="mt-2 flex justify-end">
      <SpinnerBtn @click="handleFormSubmit" :processing="form.processing" btn-text="Create" />
    </div>
  </div>
</template>
