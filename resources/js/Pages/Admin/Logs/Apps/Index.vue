<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import moment from 'moment'

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()

const props = defineProps(['apps'])
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Name') }}</th>
          <th>{{ trans('Platform') }}</th>
          <th>{{ trans('Website') }}</th>
          <th>{{ trans('Owner') }}</th>
          <th>{{ trans('Create At') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="apps.data.length" class="tbody">
        <tr v-for="(app, index) in apps.data" :key="index">
          <td>
            {{ app.name }}
          </td>
          <td>
            {{ app.platform?.name }}
          </td>
          <td>
            {{ app.site_link }}
          </td>
          <td>
            <Link :href="route('admin.users.show', app.user)">{{ app.user?.name }}</Link>
          </td>
          <td>
            {{ moment(app.created_at).format('D-MMM-Y') }}
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <a
                        class="dropdown-link"
                        @click="deleteRow(route('admin.logs.apps.destroy', app))"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                        {{ trans('Delete') }}
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
    <div class="w-full">
      <Paginate :links="apps.links" />
    </div>
  </div>
</template>
