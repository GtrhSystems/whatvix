<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'
import toast from '@/Composables/toastComposable'
defineOptions({ layout: AdminLayout })
const props = defineProps(['id', 'GOOGLE_PLACE_API_KEY'])

const form = useForm({
  GOOGLE_PLACE_API_KEY: props.GOOGLE_PLACE_API_KEY
})

function update() {
  form.put(route('admin.developer-settings.update', props.id))
}
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12">
      <div class="lg:col-span-5">
        <strong>{{ trans('Application Google Place Api Settings') }}</strong>
        <p>{{ trans('Edit your Google Place Api Settings') }}</p>
      </div>
      <div class="lg:col-span-7">
        <form @submit.prevent="update" class="space-y-4">
          <div class="card card-body space-y-4">
            <h6 class="-ml-1 text-lg">{{ trans('Google Place Api Settings') }}</h6>
            <div>
              <label class="label">{{ trans('Google Place Api Key') }}</label>
              <input
                type="text"
                v-model="form.GOOGLE_PLACE_API_KEY"
                class="input"
                placeholder="APi key"
              />
            </div>

            <div>
              <SpinnerBtn :processing="form.processing" :btn-text="trans('Save Changes')" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
