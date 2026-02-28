<script setup>
import sharedComposable from '@/Composables/sharedComposable'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import moment from 'moment'
defineOptions({ layout: AdminLayout })

const props = defineProps(['activityLog'])
const { trim } = sharedComposable()

const hiddenFields = [
  'id',
  'log_name',
  'meta',
  'uuid',
  'user_id',
  'creator_id',
  'workspace_id',
  'loggable_type',
  'loggable_id',
  'updated_at',
  'user',
  'creator',
  'workspace'
]
const objectFields = ['user', 'creator', 'workspace']
</script>

<template>
  <div class="card card-body overflow-x-auto">
    <h5 class="card-title">{{ trans('Activity Logs') }}</h5>

    <table class="table-hover table-bordered table">
      <tbody>
        <tr v-for="(value, index) in activityLog" :key="value">
          <td v-if="!hiddenFields.includes(index)">
            <span class="capitalize">{{ trim(index) }}</span>
          </td>
          <!-- others -->
          <td v-if="objectFields.includes(index)">
            <span class="capitalize">{{ trim(index) }}</span>
          </td>
          <td v-if="!hiddenFields.includes(index)">
            <pre>{{ value }}</pre>
          </td>
          <!-- others -->
          <td v-if="objectFields.includes(index)">
            <span v-for="(val, key) in value" :key="val">
              <pre>{{ key }} : {{ val }}</pre>
            </span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="capitalize">{{ trans('Meta') }}</span>
          </td>
          <td>
          
            <template v-if="activityLog.event === 'updated'">
              <template v-for="(attr, key) in activityLog.meta?.old || {}" :key="key">
                <div class="flex items-center gap-x-2">
                  <span>{{ key }} :</span>
                  <div class="text-xs">
                    <div>
                      <!-- Old value with date formatting if needed -->
                      <span class="text-red-600" v-if="key.includes('_at')">
                        {{ moment(attr).format('DD/MM/YYYY, h:mm a') }}
                      </span>
                      <span class="text-red-600" v-else>{{ attr }}</span>

                      <!-- Arrow separator -->
                      →

                      <!-- New value with date formatting if needed -->
                      <span
                        v-if="key.includes('_at') && activityLog.meta?.new?.[key]"
                        :class="{
                          'font-semibold text-green-600': activityLog.meta?.new?.[key] !== attr
                        }"
                      >
                        {{ moment(activityLog.meta.new[key]).format('DD/MM/YYYY, h:mm a') }}
                      </span>
                      <span
                        v-else
                        :class="{
                          'font-semibold text-green-600': activityLog.meta?.new?.[key] !== attr
                        }"
                      >
                        {{ activityLog.meta?.new?.[key] }}
                      </span>
                    </div>
                  </div>
                </div>
              </template>
            </template>

            <template v-else>
              <template v-for="(value, key) in activityLog.meta" :key="key">
                <div class="flex items-center gap-2">
                  <span>{{ key }} :</span>
                  <div class="text-xs">
                    <span v-if="key.includes('_at') && value">
                      {{ moment(value).format('DD/MM/YYYY, h:mm a') }}
                    </span>
                    <span v-else>{{ value }}</span>
                  </div>
                </div>
              </template>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
