<script setup>
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import AssetInput from '@/Components/Forms/AssetInput.vue'
import InputField from '@/Components/Forms/InputField.vue'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['groups', 'dialCodes', 'customer', 'groupIds'])

const form = useForm({
  name: props.customer?.name,
  picture: props.customer?.picture,
  group_ids: props.groupIds ?? [],
  dial_code: props.customer?.dial_code,
  phone: props.customer?.phone,
  _method: 'put'
})

const handleFormSubmit = () => {
  form.post(route('user.whatsapp.customers.update', props.customer))
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
        label="Select Group"
        v-model="form.group_ids"
        placeholder="SELECT"
        :validationMessage="form.errors?.group_ids"
        :options="groups"
        value-prop="value"
      />

      <div class="flex flex-col gap-2">
        <AssetInput label="Profile Image (Avatar)" v-model="form.picture" />
      </div>
    </div>

    <div class="mt-2 flex w-full justify-end">
      <SpinnerBtn @click="handleFormSubmit" :processing="form.processing" btn-text="Update" />
    </div>
  </div>
</template>
