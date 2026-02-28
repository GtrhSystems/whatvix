<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'
import toast from '@/Composables/toastComposable'

defineOptions({ layout: AdminLayout })
const props = defineProps(['id', 'EMAIL_VERIFICATION', 'PHONE_VERIFICATION'])

const form = useForm({
  EMAIL_VERIFICATION: props.EMAIL_VERIFICATION,
  PHONE_VERIFICATION: props.PHONE_VERIFICATION,
})

function update() {
  form.put(route('admin.developer-settings.update', props.id))
}
</script>
<template>
  <div class="grid grid-cols-1 lg:grid-cols-12">
    <div class="lg:col-span-5">
      <strong>{{ trans('Features Settings') }}</strong>
      <p>{{ trans('Edit Features settings') }}</p>
    </div>
    <div class="lg:col-span-7">
      <form @submit.prevent="update">
        <div class="card">
          <div class="card-body">
            <div class="mb-2">
              <label class="label mb-1">{{ trans('EMAIL VERIFICATION') }}</label>
              <select class="select" v-model="form.EMAIL_VERIFICATION">
                <option :value="true">{{ trans('Enable') }}</option>
                <option :value="false">{{ trans('Disable') }}</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="label mb-1">{{ trans('PHONE VERIFICATION') }}</label>
              <select class="select" v-model="form.PHONE_VERIFICATION">
                <option :value="true">{{ trans('Enable') }}</option>
                <option :value="false">{{ trans('Disable') }}</option>
              </select>
            </div>

            <div class="mt-3">
              <SpinnerBtn :processing="form.processing" :btn-text="trans('Save Changes')" />
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
