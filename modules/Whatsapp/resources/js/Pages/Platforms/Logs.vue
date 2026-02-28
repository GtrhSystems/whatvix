<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable.js'
import moment from 'moment'

import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['device', 'logs', 'buttons'])
const { textExcerpt } = sharedComposable()
</script>

<template>
  <div class="card mt-4">
    <div class="card-body">
      <div class="table-responsive w-full">
        <table class="table">
          <thead>
            <tr>
              <th>{{ trans('Id') }}</th>
              <th>
                {{ trans('Message Type') }}
              </th>
              <th>{{ trans('Message') }}</th>
              <th>
                {{ trans('Created At') }}
              </th>
            </tr>
          </thead>
          <tbody v-if="logs.data.length" class="tbody">
            <tr v-for="(log, index) in logs.data" :key="index">
              <td>
                {{ log?.id }}
              </td>
              <td>
                {{ log.message_type }}
              </td>
              <td>
                {{ log.message_type == 'text' ? textExcerpt(log.message_text, 50) : '' }}
              </td>
              <td>
                {{ moment(log.created_at).format('DD MMM, YY | h:mm A') }}
              </td>
            </tr>
          </tbody>
          <NoDataFound :forTable="true" v-else />
        </table>
        <Paginate v-if="logs.data.length" :links="logs.links" />
      </div>
    </div>
  </div>
</template>
