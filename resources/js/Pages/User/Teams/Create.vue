<script setup>
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import MultiSelect from '@vueform/multiselect'
import InputField from '@/Components/Forms/InputField.vue'
defineOptions({ layout: UserLayout })

const props = defineProps(['workspaces'])

const form = useForm({
  name: '',
  email: '',
  password: '',
  workspace_ids: []
})

const submit = () => {
  form.post(route('user.teams.store'))
}
</script>

<template>
  <div class="card mx-auto max-w-3xl">
    <div class="card-body">
      <form @submit.prevent="submit">
        <div class="flex flex-col gap-3">
          <h5>{{ trans('Add New Team Member') }}</h5>
          <InputField label="Full Name" type="text" v-model="form.name" placeholder="Full Name" />
          <InputField label="Email" type="email" v-model="form.email" placeholder="Email" />
          <InputField
            label="Password"
            type="password"
            v-model="form.password"
            placeholder="Password"
          />

          <div>
            <label class="label mb-1">{{ trans('Workspaces') }}</label>
            <MultiSelect
              v-model="form.workspace_ids"
              :searchable="true"
              mode="tags"
              valueProp="id"
              trackBy="name"
              label="name"
              :options="workspaces"
              placeholder="Select Workspaces"
              class="multiselect-dark"
            />
          </div>

          <div class="flex justify-end">
            <SpinnerBtn :processing="form.processing" :btn-text="trans('Create Member')" />
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
