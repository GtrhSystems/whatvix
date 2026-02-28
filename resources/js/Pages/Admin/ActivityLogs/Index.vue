<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import trans from '@/Composables/transComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps(['activityLogs'])
</script>

<template>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Subject') }}</th>
          <th>{{ trans('User') }}</th>
          <th>{{ trans('Creator') }}</th>
          <th>{{ trans('Description') }}</th>
          <th>{{ trans('Workspace') }}</th>
          <th>{{ trans('Created at') }}</th>
          <th class="flex justify-end">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>

      <tbody v-if="activityLogs.total > 0">
        <tr v-for="activityLog in activityLogs.data" :key="activityLog.id">
          <td>{{ activityLog.log_name }}</td>
          <td>
            <strong>{{ activityLog.user?.name }}</strong> <br />
            {{ activityLog.user?.email }}
          </td>
          <td>
            <strong>{{ activityLog.creator?.name }}</strong>
            <br />
            {{ activityLog.creator?.email }}
          </td>
          <td>{{ activityLog.description }}</td>
          <td>{{ activityLog.workspace?.name }}</td>
          <td class="text-left">
            {{ moment(activityLog.created_at).format('DD MMM,YYYY h:mm A') }}
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="text-2xl" icon="bx:dots-horizontal-rounded" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link
                        :href="route('admin.activity-logs.show', activityLog)"
                        class="dropdown-link"
                      >
                        <Icon icon="bx:show" />
                        <span>{{ trans('Details') }}</span>
                      </Link>
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
    <Paginate :links="activityLogs.links" />
  </div>
</template>
