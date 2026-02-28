<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import trans from '@/Composables/transComposable'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

defineOptions({ layout: AdminLayout })
const { textExcerpt, deleteRow } = sharedComposable()
const props = defineProps(['posts', 'request'])

const selectOptions = [
  {
    label: 'Title',
    value: 'title'
  }
]
</script>

<template>
  <FilterDropdown :options="selectOptions" />

  <!-- Customer Table Starts -->
  <div class="table-responsive mt-2 whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th class="col-3">{{ trans('ID') }}</th>
          <th class="col-3">{{ trans('Title') }}</th>
          <th class="col-1">{{ trans('Status') }}</th>
          <th class="col-2">{{ trans('Created At') }}</th>
          <th class="col-1">
            <div class="text-right">{{ trans('Action') }}</div>
          </th>
        </tr>
      </thead>
      <tbody v-if="posts.data != 0">
        <tr v-for="blog in posts.data" :key="blog.id">
          <td>{{ blog.id }}</td>
          <td class="flex">
            <img
              v-if="blog.preview?.value"
              v-lazy="blog.preview?.value"
              class="avatar rounded-square mr-3"
            />
            <a target="_blank" :href="`/blogs/${blog.slug}`">{{ textExcerpt(blog.title, 80) }}</a>
          </td>

          <td class="text-left">
            <span class="badge" :class="blog.status == 1 ? 'badge-success' : 'badge-danger'">
              {{ blog.status == 1 ? trans('Active') : trans('Draft') }}
            </span>
          </td>
          <td>
            {{ moment(blog.created_at).format('D-MMM-Y') }}
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link :href="route('admin.blog.edit', blog.id)" class="dropdown-link">
                        <Icon class="h-6 text-slate-400" icon="material-symbols:edit-outline" />
                        <span>{{ trans('Edit') }}</span>
                      </Link>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link"
                        @click="deleteRow(route('admin.blog.destroy', blog.slug))"
                      >
                        <Icon class="h-6" icon="material-symbols:delete-outline" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound v-else for-table="true" />
    </table>

    <Paginate v-if="posts.data.length != 0" :links="posts.links" />
  </div>
  <!-- Customer Table Ends -->
</template>
