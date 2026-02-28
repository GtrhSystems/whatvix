<script setup>
import UserLayout from '@/Layouts/User/UserLayout.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
const { formatCurrency } = sharedComposable()
defineOptions({ layout: UserLayout })
const props = defineProps(['creditLogs', 'credit_fee', 'gateways'])
</script>

<template>
  <div v-if="creditLogs.total" class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">{{ trans('Transaction No') }}</th>
          <th scope="col">{{ trans('Credits') }}</th>
          <th scope="col">{{ trans('Price') }}</th>
          <th scope="col">{{ trans('Status') }}</th>
          <th scope="col">{{ trans('Gateway') }}</th>
          <th scope="col">{{ trans('Date') }}</th>
        </tr>
      </thead>
      <tbody class="border-0">
        <tr
          :class="{
            active: creditLog.status == 1,
            pending: creditLog.status != 1
          }"
          v-for="creditLog in creditLogs.data"
          :key="creditLog.id"
        >
          <td>
            {{ creditLog.invoice_no }}
          </td>
          <td>
            {{ creditLog.credits }}
          </td>
          <td>
            {{ formatCurrency(creditLog.price) }}
          </td>
          <td>
            <span v-if="creditLog.status == 1" class="badge badge-primary">
              {{ trans('Complete') }}
            </span>
            <span v-else-if="creditLog.status == 0" class="badge badge-warning">
              {{ trans('Pending') }}
            </span>
          </td>
          <td>
            {{ creditLog.gateway.name }}
          </td>
          <td>
            <p>
              {{ moment(creditLog.updated_at).format('DD MMM Y') }}
            </p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <NoDataFound v-else />

  <Paginate :links="creditLogs.links" />
</template>
