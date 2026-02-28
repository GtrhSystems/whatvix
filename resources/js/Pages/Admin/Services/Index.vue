<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { useForm, router } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import sharedComposable from '@/Composables/sharedComposable'
import Overview from '@/Components/Dashboard/OverviewGrid.vue'

import { onMounted, computed } from 'vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import trans from '@/Composables/transComposable'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()

const props = defineProps(['services', 'total', 'active', 'inActive', 'buttons'])
const stats = computed(() => {
  return [
    {
      value: props.total,
      title: trans('Total'),
      iconClass: 'bx bx-badge'
    },
    {
      value: props.active,
      title: trans('Active'),
      iconClass: 'bx bx-badge-check'
    },
    {
      value: props.inActive,
      title: trans('Inactive'),
      iconClass: 'bx bx-x-circle'
    }
  ]
})

const filterOptions = [
  {
    label: 'Title',
    value: 'title'
  },
  {
    label: 'Overview',
    value: 'overview'
  },
  {
    label: 'Status',
    value: 'is_active',
    options: [
      {
        label: 'Active',
        value: 1
      },
      {
        label: 'Inactive',
        value: 0
      }
    ]
  },
  {
    label: 'Is Featured',
    value: 'is_featured',
    options: [
      {
        label: 'Active',
        value: 1
      },
      {
        label: 'Inactive',
        value: 0
      }
    ]
  }
]
</script>

<template>
  <div class="space-y-6">
    <FilterDropdown :options="filterOptions" />

    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Icon') }}</th>
            <th>{{ trans('Title') }}</th>
            <th>{{ trans('Category') }}</th>
            <th>{{ trans('Is Active') }}</th>
            <th>{{ trans('Is Featured') }}</th>
            <th>{{ trans('Created At') }}</th>
            <th>
              <p class="text-right">
                {{ trans('Action') }}
              </p>
            </th>
          </tr>
        </thead>

        <tbody v-if="services.total">
          <tr v-for="item in services.data" :key="item.id">
            <td class="text-left">
              <img v-lazy="item.icon" class="h-12 rounded" alt="icon" />
            </td>
            <td>{{ item.title }}</td>
            <td>{{ item.category?.title }}</td>

            <td class="text-left">
              <span class="badge" :class="item.is_active ? 'badge-success' : 'badge-danger'">
                {{ item.is_active ? trans('Active') : trans('Draft') }}
              </span>
            </td>
            <td class="text-left">
              <span class="badge" :class="item.is_featured ? 'badge-success' : ''">
                {{ item.is_featured ? trans('Featured') : '-' }}
              </span>
            </td>
            <td>
              {{ moment(item.created_at).format('D-MMM-Y') }}
            </td>
            <td class="">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link :href="route('admin.services.edit', item)" class="dropdown-link">
                        <Icon class="w-30 text-lg" icon="bx:edit" />
                        <span>{{ trans('Edit') }}</span>
                      </Link>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        as="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.services.destroy', item.id))"
                      >
                        <Icon class="w-30 text-lg" icon="bx:trash" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else for-table="true" />
      </table>
    </div>

    <Paginate :links="services.links" />
  </div>
</template>
