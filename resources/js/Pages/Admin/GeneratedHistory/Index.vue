<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

defineOptions({ layout: AdminLayout })
const { formatCurrency, textExcerpt, trim } = sharedComposable()

const props = defineProps([
  'aiGenerated',
  'total',
  'totalCharges',
  'totalResults',
  'buttons',
  'segments',
  'request',
  'type'
])
</script>

<template>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('User Name') }}</th>
          <th>{{ trans('Charge') }}</th>

          <th>
            {{ trans('Result') }}
          </th>
          <th>
            {{ trans('Length') }}
          </th>
          <th>
            {{ trans('Content') }}
          </th>

          <th>
            <p class="text-end">{{ trans('Date') }}</p>
          </th>
        </tr>
      </thead>

      <tbody v-if="aiGenerated.total > 0">
        <tr v-for="generated in aiGenerated.data" :key="generated.id">
          <td>
            {{ generated.user?.name }}
          </td>
          <td>
            {{ formatCurrency(generated.charge) }}
          </td>
          <td>
            {{ generated.result }}
          </td>

          <td>
            {{ generated.length }}
          </td>
          <td>
            <Link
              class="underline"
              :href="route('admin.ai-generated-history.edit', generated.uuid)"
            >
              {{ textExcerpt(generated.content, 100) }}
            </Link>
          </td>

          <td class="text-left">
            <p class="text-end">{{ moment(generated.updated_at).format('DD MMM, YYYY') }}</p>
          </td>
        </tr>
      </tbody>
      <NoDataFound v-else for-table="true" />
    </table>
  </div>
  <Paginate :links="aiGenerated.links" />
</template>
