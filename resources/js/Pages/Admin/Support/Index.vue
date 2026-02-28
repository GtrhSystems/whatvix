<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import Overview from '@/Components/Dashboard/OverviewGrid.vue'
import moment from 'moment'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import trans from '@/Composables/transComposable'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import sharedComposable from '@/Composables/sharedComposable'
defineOptions({ layout: AdminLayout })

const { textExcerpt } = sharedComposable()
const props = defineProps(['request', 'supports', 'type'])

const filterOptions = [
  {
    label: 'Ticket No',
    value: 'ticket_no'
  },
  {
    label: 'Subject',
    value: 'subject'
  },
  {
    label: 'User Email',
    value: 'user_email'
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Open',
        value: 0
      },
      {
        label: 'Pending',
        value: 1
      },
      {
        label: 'Closed',
        value: 2
      }
    ]
  }
]
</script>

<template>
  <div class="mt-4 space-y-4">
    <FilterDropdown :options="filterOptions" />

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
            <th>{{ trans('User') }}</th>
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
              <Link :href="'/admin/support/' + support.id">
                {{ support.ticket_no }}
              </Link>
            </td>
            <td>
              <a class="text-dark" :href="'/admin/support/' + support.id">
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
                {{
                  trans(support.status == 2 ? 'pending' : support.status == 1 ? 'Open' : 'Closed')
                }}
              </span>
            </td>
            <td class="text-center">
              <a :href="'/admin/users/' + support.user_id" class="text-dark">
                {{ support.user?.name ?? '' }}
              </a>
            </td>
            <td class="text-center">
              {{ moment(support.created_at).format('MMM Do YYYY') }}
            </td>
            <td>
              <div class="flex justify-end">
                <Link :href="'/admin/support/' + support.id" class="btn btn-primary btn-sm">
                  {{ trans('View') }}
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else for-table="true" />
      </table>

      <Paginate :links="supports.links" />
    </div>
  </div>
</template>
