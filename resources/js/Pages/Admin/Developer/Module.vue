<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
defineOptions({ layout: AdminLayout })

const form = useForm({
  module: null,
  purchase_key: ''
})
const submitForm = () => {
  form.post(route('admin.module-developer-settings.store'), {
    preserveState: false,
    preserveScroll: true
  })
}
</script>

<template>
  <div class="mx-auto max-w-6xl">
    <div class="card card-body">
      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <label class="label mb-1" for="module"> {{ trans('Upload Module') }} </label>
          <input
            class="input"
            id="module"
            type="file"
            accept=".zip"
            @change="(e) => (form.module = e.target.files[0])"
            required
          />
        </div>
        <div class="mb-6">
          <label class="label mb-1" for="purchase_key"> {{ trans('Purchase Key') }} </label>
          <input class="input" id="purchase_key" type="text" v-model="form.purchase_key" required />
        </div>
        <div class="flex items-center justify-between">
          <button class="btn btn-primary" :disabled="form.processing" type="submit">
            {{ trans('Upload') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
