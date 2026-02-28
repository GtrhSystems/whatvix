<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'
import toast from '@/Composables/toastComposable'

defineOptions({ layout: AdminLayout })
const props = defineProps(['id', 'TWILIO_ACCOUNT_SID', 'TWILIO_AUTH_TOKEN', 'TWILIO_NUMBER'])

const form = useForm({
  TWILIO_ACCOUNT_SID: props.TWILIO_ACCOUNT_SID,
  TWILIO_AUTH_TOKEN: props.TWILIO_AUTH_TOKEN,
  TWILIO_NUMBER: props.TWILIO_NUMBER
})

function update() {
  form.put(route('admin.developer-settings.update', props.id))
}
</script>
<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12">
      <div class="lg:col-span-5">
        <strong>{{ trans('Twilio Settings') }}</strong>
        <p>{{ trans('Edit Twilio Api settings') }}</p>
      </div>
      <div class="lg:col-span-7">
        <form @submit.prevent="update">
          <div class="card">
            <div class="card-body">
              <div class="mb-2">
                <label class="label mb-1">{{ trans('TWILIO ACCOUNT SID') }}</label>
                <input type="text" v-model="form.TWILIO_ACCOUNT_SID" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label mb-1">{{ trans('TWILIO AUTH TOKEN') }}</label>
                <input type="text" v-model="form.TWILIO_AUTH_TOKEN" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label mb-1">{{ trans('TWILIO NUMBER') }}</label>
                <input type="text" v-model="form.TWILIO_NUMBER" required class="input" />
              </div>

              <div class="mt-3">
                <SpinnerBtn :processing="form.processing" :btn-text="trans('Save Changes')" />
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
