<script setup>
import UserLayout from '@/Layouts/User/UserLayout.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import sharedComposable from '@/Composables/sharedComposable'
import moment from 'moment'
import trans from '@/Composables/transComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
defineOptions({ layout: UserLayout })

const props = defineProps(['aiGenerated'])

const { textExcerpt, sanitizeHtml } = sharedComposable()
const filterOptions = [
  {
    label: 'Content',
    value: 'content'
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Active',
        value: 'active'
      },
      {
        label: 'Inactive',
        value: 'inactive'
      }
    ]
  }
]
</script>

<template>
  <FilterDropdown :options="filterOptions" />

  <div class="table-responsive mt-4 whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Charge') }}</th>

          <th>
            {{ trans('Total Words') }}
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
            {{ generated.charge }}
          </td>

          <td>
            {{ generated.length }}
          </td>
          <td>
            <Link :href="route('user.ai-generated-history.edit', generated.uuid)">
              {{ textExcerpt(generated.content, 100) }}
            </Link>
          </td>

          <td class="text-left">
            <p class="text-end">{{ moment(generated.updated_at).format('DD MMM, YYYY h:mm A') }}</p>
          </td>
        </tr>
      </tbody>
      <NoDataFound v-else :for-table="true" />
    </table>
  </div>
  <Paginate :links="aiGenerated.links" />
</template>
