<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import moment from 'moment'

defineOptions({ layout: AdminLayout })
const props = defineProps(['overviews', 'templates'])
const { deleteRow } = sharedComposable()
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Template Name') }}
          </th>
          <th>
            {{ trans('Device Name') }}
          </th>
          <th>{{ trans('Type') }}</th>
          <th>{{ trans('Owner') }}</th>
          <th>{{ trans('Status') }}</th>
          <th>{{ trans('Created At') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="templates.data.length" class="tbody">
        <tr v-for="(template, index) in templates.data" :key="index">
          <td>
            {{ template.name }}
          </td>
          <td>
            {{ template.platform?.name ?? 'NA' }}
          </td>
          <td>
            {{ template.type }}
          </td>
          <td>
            <Link :href="route('admin.users.show', template.owner)">
              {{ template.owner?.name }}
            </Link>
          </td>
          <td>
            <span
              class="badge uppercase"
              :class="
                ['APPROVED', 'active'].includes(template.status) ? 'badge-success' : 'badge-warning'
              "
            >
              {{ template.status }}</span
            >
          </td>
          <td>
            {{ moment(template.created_at).format('D-MMM-Y') }}
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
                      <button
                        class="dropdown-link delete-confirm"
                        @click="deleteRow(route('admin.logs.templates.destroy', template.id))"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                        {{ trans('Delete') }}
                      </button>
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
  </div>
  <Paginate :links="templates.links" />
</template>
