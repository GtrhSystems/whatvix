<script setup>
import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import moment from 'moment'

const { deleteRow } = sharedComposable()
defineOptions({ layout: AdminLayout })
const props = defineProps(['platforms'])
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Platform') }}</th>
          <th>{{ trans('Module') }}</th>
          <th>{{ trans('Owner') }}</th>
          <th>{{ trans('Status') }}</th>
          <th>{{ trans('Created At') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="platforms.data.length" class="tbody">
        <tr v-for="(platform, index) in platforms.data" :key="index">
          <td>{{ platform.name }}</td>
          <td>{{ platform.module }}</td>
          <td>
            <Link :href="route('admin.users.show', platform.owner)">{{
              platform.owner?.name
            }}</Link>
          </td>
          <td>
            <span
              v-if="platform.status"
              class="badge"
              :class="platform.status == 'connected' ? 'badge-success' : 'badge-danger'"
            >
              {{ platform.status }}
            </span>

            <span v-else> - </span>
          </td>
          <td>
            {{ moment(platform.created_at).format('D-MMM-Y') }}
          </td>
          <td class="!text-right">
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
                        @click="deleteRow(route('admin.logs.platforms.destroy', platform))"
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
      <Paginate :links="platforms.links" />
    </div>
  </div>
</template>
