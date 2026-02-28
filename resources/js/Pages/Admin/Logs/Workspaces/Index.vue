<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import moment from 'moment'

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()

const props = defineProps(['workspaces'])
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Name') }}</th>
          <th>{{ trans('Description') }}</th>
          <th>{{ trans('Modules') }}</th>
          <th>{{ trans('Members') }}</th>
          <th>{{ trans('Owner') }}</th>
          <th>{{ trans('Create At') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="workspaces.data.length" class="tbody">
        <tr v-for="(workspace, index) in workspaces.data" :key="index">
          <td>
            {{ workspace.name }}
          </td>
          <td>
            {{ workspace.description || 'N/A' }}
          </td>
          <td>
            <p class="truncate whitespace-nowrap">
              {{ workspace.modules?.join(', ') }}
            </p>
          </td>
          <td>
            <Link
              :href="
                route('admin.logs.members.index', {
                  workspace_id: workspace.id
                })
              "
            >
              {{ workspace.members_count ?? 0 }} {{ trans('Members') }}
            </Link>
          </td>
          <td>
            <Link :href="route('admin.users.show', workspace.owner)">{{
              workspace.owner?.name
            }}</Link>
          </td>
          <td>
            {{ moment(workspace.created_at).format('D-MMM-Y') }}
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
                        @click="deleteRow(route('admin.logs.workspaces.destroy', workspace))"
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
      <Paginate :links="workspaces.links" />
    </div>
  </div>
</template>
