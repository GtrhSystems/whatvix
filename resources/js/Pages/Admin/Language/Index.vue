<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { Link, useForm } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })

const { deleteRow } = sharedComposable()

const props = defineProps(['languages', 'countries'])

const form = useForm({
  name: '',
  language_code: ''
})

const storeLanguage = () => {
  form.post(route('admin.language.store'), {
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th class="col-2">{{ trans('Language Name') }}</th>
            <th class="col-2">{{ trans('Language Key') }}</th>
            <th class="col-8">
              <div class="text-right">
                {{ trans('Action') }}
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(language, key) in languages" :key="key">
            <td class="text-left">
              {{ language }}
            </td>
            <td class="text-left">
              {{ key }}
            </td>
            <td class="flex justify-end gap-3">
              <Link :href="route('admin.language.show', key)" class="btn btn-secondary">
                <Icon class="h-6" icon="material-symbols:edit-outline" />
              </Link>
              <button
                type="button"
                class="delete-confirm btn btn-primary"
                @click="deleteRow(route('admin.language.show', key))"
              >
                <Icon class="h-6" icon="material-symbols:delete-outline" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Add New Language')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="form.processing"
    @action="storeLanguage"
  >
    <div class="mb-2">
      <label>{{ trans('Language Name') }}</label>
      <input
        v-model="form.name"
        type="text"
        name="name"
        class="input"
        required
        placeholder="English"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Select Language') }}</label>
      <select v-model="form.language_code" class="select" name="language_code">
        <option v-for="country in countries" :key="country" :value="country.code">
          {{ country.name }}
        </option>
      </select>
    </div>
  </Modal>
</template>
