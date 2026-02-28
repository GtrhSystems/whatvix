<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })
const props = defineProps([
  'id',
  'BROADCAST_DRIVER',
  'PUSHER_APP_ID',
  'PUSHER_APP_KEY',
  'PUSHER_APP_SECRET',
  'PUSHER_SCHEME',
  'PUSHER_APP_CLUSTER'
])

const form = useForm({
  BROADCAST_DRIVER: props.BROADCAST_DRIVER ?? 'pusher',
  PUSHER_APP_ID: props.PUSHER_APP_ID ?? '',
  PUSHER_APP_KEY: props.PUSHER_APP_KEY ?? '',
  PUSHER_APP_SECRET: props.PUSHER_APP_SECRET ?? '',
  PUSHER_SCHEME: props.PUSHER_SCHEME ?? 'http',
  PUSHER_APP_CLUSTER: props.PUSHER_APP_CLUSTER ?? 'mt1'
})

function update() {
  form.put(route('admin.developer-settings.update', props.id))
}
</script>
<template>
  <div class="grid grid-cols-1 lg:grid-cols-12">
    <div class="lg:col-span-5">
      <strong>{{ trans('Broadcast Settings') }}</strong>
      <p>{{ trans('Edit Broadcast settings') }}</p>
    </div>
    <div class="lg:col-span-7">
      <form @submit.prevent="update">
        <div class="card">
          <div class="card-body">
            <div class="mb-2">
              <label class="label mb-1">{{ trans('Broadcast Driver') }}</label>
              <select class="select" v-model="form.BROADCAST_DRIVER">
                <option value="log">{{ trans('Log') }}</option>
                <option value="pusher">{{ trans('Pusher') }}</option>
              </select>
            </div>

            <template v-if="form.BROADCAST_DRIVER == 'pusher'">
              <div class="mb-2">
                <label class="label mb-1">{{ trans('Pusher App ID') }}</label>
                <input type="text" v-model="form.PUSHER_APP_ID" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label mb-1">{{ trans('Pusher App Key') }}</label>
                <input type="text" v-model="form.PUSHER_APP_KEY" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label mb-1">{{ trans('Pusher App Secret') }}</label>
                <input type="text" v-model="form.PUSHER_APP_SECRET" required class="input" />
              </div>

              <div class="mb-2">
                <label class="label mb-1">{{ trans('Pusher App Cluster') }}</label>
                <input type="text" v-model="form.PUSHER_APP_CLUSTER" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label mb-1">{{ trans('Pusher App Scheme') }}</label>
                <select class="select" v-model="form.PUSHER_SCHEME">
                  <option value="http">{{ trans('http') }}</option>
                  <option value="https">{{ trans('https') }}</option>
                </select>
              </div>
            </template>

            <div class="mt-3">
              <SpinnerBtn :processing="form.processing" :btn-text="trans('Save Changes')" />
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
