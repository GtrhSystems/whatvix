<script setup>
import moment from 'moment'

import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'

const props = defineProps(['bulkSends'])
defineOptions({ layout: UserLayout })
const { badgeClass } = sharedComposable()
</script>

<template>
  <div class="table-responsive mt-6 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Recipient Number') }}</th>
          <th>
            {{ trans('Device') }}
          </th>
          <th>
            {{ trans('Template') }}
          </th>
          <th>
            {{ trans('Message Type') }}
          </th>

          <th>
            {{ trans('Status') }}
          </th>
          <th class="!text-right">
            {{ trans('Date') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="bulkSends.data.length" class="tbody">
        <tr v-for="(bulk_send, index) in bulkSends.data" :key="index">
          <td>
            {{ bulk_send.recipient_number }}
          </td>
          <td>
            {{ bulk_send.platform?.name || trans('None') }}
          </td>
          <td>
            {{ bulk_send?.template?.name || trans('None') }}
          </td>

          <td class="capitalize">
            {{ bulk_send.message_type || trans('None') }}
          </td>

          <td>
            <span :class="badgeClass(bulk_send.status)">{{ bulk_send.status }}</span>
          </td>
          <td class="!text-right">
            {{ moment(bulk_send.created_at).format('DD MMM, YYYY | h:mm A') }}
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>

    <Paginate :links="bulkSends.links" />
  </div>
</template>
