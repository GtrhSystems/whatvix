<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })
const props = defineProps([
  'id',
  'PORT',
  'NODE_ENV',

  'ENABLE_WEBHOOK',
  'ENABLE_WEBSOCKET',
  'BOT_NAME',
  'DATABASE_URL',
  'LOG_LEVEL',
  'RECONNECT_INTERVAL',
  'MAX_RECONNECT_RETRIES',
  'SSE_MAX_QR_GENERATION',
  'SESSION_CONFIG_ID',
  'API_KEY'
])

const form = useForm({
  PORT: props.PORT,
  NODE_ENV: props.NODE_ENV,

  ENABLE_WEBHOOK: props.ENABLE_WEBHOOK,
  ENABLE_WEBSOCKET: props.ENABLE_WEBSOCKET,
  BOT_NAME: props.BOT_NAME,
  DATABASE_URL: props.DATABASE_URL,
  LOG_LEVEL: props.LOG_LEVEL,
  RECONNECT_INTERVAL: props.RECONNECT_INTERVAL,
  MAX_RECONNECT_RETRIES: props.MAX_RECONNECT_RETRIES,
  SSE_MAX_QR_GENERATION: props.SSE_MAX_QR_GENERATION,
  SESSION_CONFIG_ID: props.SESSION_CONFIG_ID,
  API_KEY: props.API_KEY
})

function update() {
  form.put(route('admin.developer-settings.update', props.id))
}
</script>
<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12">
      <div class="lg:col-span-5">
        <strong>{{ trans('Whatsapp Server Settings') }}</strong>
        <p>{{ trans('Edit Whatsapp Server settings') }}</p>
      </div>
      <div class="lg:col-span-7">
        <form @submit.prevent="update">
          <div class="card">
            <div class="card-body">
              <div class="mb-2">
                <label class="label">{{ trans('PORT') }}</label>
                <input type="text" v-model="form.PORT" class="input" required />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('NODE ENV') }}</label>
                <select class="select" v-model="form.NODE_ENV">
                  <option value="development">
                    {{ trans('Development') }}
                  </option>
                  <option value="production">
                    {{ trans('Production') }}
                  </option>
                </select>
              </div>

              <div class="mb-2">
                <label class="label">{{ trans('ENABLE WEBHOOK') }}</label>
                <select class="select" v-model="form.ENABLE_WEBHOOK">
                  <option value="true">
                    {{ trans('Yes') }}
                  </option>
                  <option value="false">
                    {{ trans('No') }}
                  </option>
                </select>
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('ENABLE WEBSOCKET') }}</label>
                <select class="select" v-model="form.ENABLE_WEBSOCKET">
                  <option value="true">
                    {{ trans('Yes') }}
                  </option>
                  <option value="false">
                    {{ trans('No') }}
                  </option>
                </select>
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('BOT NAME') }}</label>
                <input type="text" v-model="form.BOT_NAME" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('DATABASE URL') }}</label>
                <input type="text" disabled :value="form.DATABASE_URL" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('LOG LEVEL') }}</label>
                <select class="select" v-model="form.LOG_LEVEL">
                  <option value="error">
                    {{ trans('Error') }}
                  </option>
                  <option value="warn">
                    {{ trans('Warn') }}
                  </option>
                  <option value="info">
                    {{ trans('Info') }}
                  </option>
                  <option value="debug">
                    {{ trans('Debug') }}
                  </option>
                </select>
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('RECONNECT INTERVAL') }}</label>
                <input type="text" v-model="form.RECONNECT_INTERVAL" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('MAX RECONNECT RETRIES') }}</label>
                <input type="text" v-model="form.MAX_RECONNECT_RETRIES" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('SSE MAX QR GENERATION') }}</label>
                <input type="text" v-model="form.SSE_MAX_QR_GENERATION" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('SESSION CONFIG ID') }}</label>
                <input type="text" v-model="form.SESSION_CONFIG_ID" required class="input" />
              </div>
              <div class="mb-2">
                <label class="label">{{ trans('API KEY') }}</label>
                <input type="text" v-model="form.API_KEY" required class="input" />
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
