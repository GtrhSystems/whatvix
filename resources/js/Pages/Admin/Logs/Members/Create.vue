<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import MultiSelect from '@vueform/multiselect'
import InputField from '@/Components/Forms/InputField.vue'
defineOptions({ layout: AdminLayout })

const props = defineProps(['owners', 'member'])

const isEdit = computed(() => !!props.member?.id)

const form = useForm({
  owner_id: '',
  workspace_ids: [],
  name: props.member?.name ?? '',
  email: props.member?.email ?? '',
  password: '',
  _method: props.member?.id ? 'put' : 'post'
})

const findOwner = computed(() => {
  return props.owners?.find((owner) => owner.id == form.owner_id)
})

const getOwnerWorkspaces = computed(() => {
  return findOwner.value?.my_workspaces
})

const submit = () => {
  isEdit.value
    ? form.put(route('admin.logs.members.update', props.member.id))
    : form.post(route('admin.logs.members.store'))
}
</script>

<template>
  <div class="card mx-auto max-w-3xl">
    <div class="card-body">
      <form @submit.prevent="submit">
        <div class="flex flex-col gap-3">
          <h5>{{ isEdit ? 'Edit Member' : 'Add New Member' }}</h5>
          <MultiSelect
            v-if="!isEdit"
            v-model="form.owner_id"
            :searchable="true"
            valueProp="id"
            trackBy="id"
            label="name"
            :options="owners"
            placeholder="Select Owner"
            class="multiselect-dark"
          />

          <div v-if="!isEdit">
            <label class="label mb-1">{{ trans('Workspaces') }}</label>
            <MultiSelect
              v-model="form.workspace_ids"
              :searchable="true"
              mode="tags"
              valueProp="id"
              trackBy="id"
              label="name"
              :options="getOwnerWorkspaces"
              placeholder="Select Workspaces"
              class="multiselect-dark"
            />
          </div>

          <InputField label="Full Name" type="text" v-model="form.name" placeholder="Full Name" />
          <InputField label="Email" type="email" v-model="form.email" placeholder="Email" />
          <InputField
            label="Password"
            type="password"
            v-model="form.password"
            placeholder="Password"
          />

          <div class="flex justify-end">
            <SpinnerBtn
              :processing="form.processing"
              :btn-text="isEdit ? trans('Update') : trans('Create Member')"
            />
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
