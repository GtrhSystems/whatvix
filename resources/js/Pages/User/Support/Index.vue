<script setup>
import moment from 'moment'
import NoDataFound from '@/Components/NoDataFound.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import sharedComposable from '@/Composables/sharedComposable'
import trans from '@/Composables/transComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const { textExcerpt } = sharedComposable()

const props = defineProps(['supports'])
</script>

<template>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Ticket No') }}</th>
          <th>{{ trans('Subject') }}</th>
          <th>
            {{ trans('Conversations') }}
          </th>
          <th>{{ trans('Status') }}</th>
          <th>
            {{ trans('Created At') }}
          </th>
          <th>
            <p class="text-end">{{ trans('Ticket') }}</p>
          </th>
        </tr>
      </thead>

      <tbody v-if="supports.total">
        <tr v-for="support in supports.data" :key="support.id">
          <td class="text-center">
            {{ support.ticket_no }}
          </td>
          <td>
            <a class="text-dark" :href="'/user/supports/' + support.id">
              {{ textExcerpt(support.subject, 50) }}
            </a>
          </td>
          <td class="text-center">
            {{ support.conversations_count }}
          </td>
          <td>
            <span
              :class="
                support.status == 2
                  ? 'badge badge-warning'
                  : support.status == 1
                  ? 'badge badge-success'
                  : 'badge badge-danger'
              "
            >
              {{ trans(support.status == 2 ? 'pending' : support.status == 1 ? 'Open' : 'Closed') }}
            </span>
          </td>

          <td class="text-center">
            {{ moment(support.created_at).format('MMM Do YYYY') }}
          </td>
          <td>
            <div class="flex justify-end">
              <Link :href="'/user/supports/' + support.id" class="btn btn-primary btn-sm">
                {{ trans('View') }}
              </Link>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound v-else for-table="true" />
    </table>

    <Paginate v-if="supports.data.length != 0" :links="supports.links" />
  </div>
</template>
