<script setup>
import { onMounted, ref } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import sharedComposable from '@/Composables/sharedComposable'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useUserDashboardStore } from '@/Store/userDashboardStore'
import OverviewGrid from '@/Components/Dashboard/OverviewGrid.vue'
import OverviewSkeleton from '@/Components/Dashboard/Skeleton/OverviewCard.vue'
import CampaignCardSkeleton from '@/Components/Dashboard/Skeleton/CampaignCard.vue'
import { storeToRefs } from 'pinia'
import moment from 'moment'

defineOptions({ layout: AdminLayout })
const props = defineProps(['singleUser', 'orders', 'activityLogs'])
const dashboardStore = useUserDashboardStore()
const { deleteRow, formatCurrency, textExcerpt, uiAvatar, badgeClass } = sharedComposable()

const { analytics, dateFilterQueries, filterForm, loading, storageChart } =
  storeToRefs(dashboardStore)
const user = ref(props.singleUser)

onMounted(() => {
  dashboardStore.fetchAnalytics({ type: 'overviews', user_id: user.value.id })
  dashboardStore.fetchAnalytics({ type: 'storage', user_id: user.value.id })
  dashboardStore.fetchAnalytics({ type: 'plan_data', user_id: user.value.id })
})
</script>

<template>
  <OverviewSkeleton v-if="analytics.overviews.loading" :skeleton-count="8" />
  <OverviewGrid :items="analytics.overviews.data" />
  <div class="mt-4 space-y-4">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
      <section class="card card-body col-span-1 flex flex-col justify-center gap-2">
        <!-- User Avatar & Status  -->

        <div class="flex items-start gap-4">
          <!-- Avatar Section -->
          <div class="relative flex h-16 w-16 flex-col items-center gap-3">
            <img
              v-lazy="uiAvatar(user.name, user.avatar)"
              alt="User avatar"
              class="h-full w-full rounded-full border-2 border-gray-100 object-cover"
            />
            <span
              :class="user.status === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              class="rounded-full px-2 py-1 text-xs font-medium"
            >
              {{ user.status === 1 ? trans('Active') : trans('Suspended') }}
            </span>
          </div>

          <!-- User Info Section -->
          <div class="flex flex-grow flex-col">
            <h3 class="text-lg font-medium text-gray-900">{{ user.name }}</h3>

            <p class="mb-2 text-sm text-gray-500">{{ user.email }}</p>

            <div class="mt-auto flex items-center text-xs text-gray-500">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mr-1 h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              {{ trans('Joined: ') }}{{ moment(user.created_at).format('MMM D, YYYY') }}
            </div>
          </div>
        </div>
      </section>
      <div
        class="card card-body col-span-2 flex items-center justify-between rounded-2xl"
        v-if="!loading"
      >
        <div class="flex flex-col">
          <p class="text-lg font-semibold leading-6">
            {{ trans('Active Subscription') }}
            <span v-if="analytics.plan_data.data && user.will_expire" class="text-xs">
              ( {{ analytics.plan_data.data?.plan_title }} )
            </span>
          </p>
          <p class="text-sm dark:text-dark-400" v-if="user.will_expire">
            {{ trans('Your subscription expires on') }}
            <span>
              {{ moment(user.will_expire).format('MMM DD, YYYY') }}
            </span>
          </p>
          <p class="text-sm text-red-500 dark:text-red-500/80" v-else>
            {{ trans('No active subscription.') }}
          </p>
        </div>
        <div
          class="flex min-h-16 min-w-16 items-center justify-center rounded-full bg-primary-600/60"
        >
          <Icon icon="solar:calendar-broken" class="size-8 text-gray-100" />
        </div>
      </div>
      <CampaignCardSkeleton v-if="analytics.storage.loading && !analytics.storage.data" />
      <div class="card flex h-44 flex-col items-center justify-center rounded-2xl" v-else>
        <VueApexCharts
          v-if="$el"
          width="100%"
          height="300"
          type="radialBar"
          :options="storageChart.chartOptions"
          :series="storageChart.series"
        />
      </div>
    </div>

    <div class="table-responsive whitespace-nowrap rounded-primary">
      <h5 class="p-2">{{ trans('Orders') }}</h5>
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Order No') }}</th>
            <th>{{ trans('Plan Name') }}</th>
            <th>{{ trans('Payment Mode') }}</th>
            <th>{{ trans('Amount') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Created At') }}</th>
          </tr>
        </thead>
        <tbody v-if="orders.total">
          <tr v-for="order in orders.data" :key="order.id">
            <td>
              <Link
                :href="'/admin/order/' + order.id"
                class="text-sm font-medium text-primary-500 transition duration-150 ease-in-out hover:underline"
              >
                {{ order.invoice_no }}
              </Link>
            </td>
            <td>{{ order.plan.title }}</td>
            <td>{{ order.gateway.name }}</td>
            <td>{{ formatCurrency(order.amount) }}</td>
            <td>
              <div class="capitalize" :class="badgeClass(order.status)">
                {{
                  order.status
                }}
              </div>
            </td>
            <td class="text-center">
              {{ order.created_at_diff }}
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else for-table="true" />
      </table>
      <Paginate :links="orders.links" :preserveState="true" :preserveScroll="true" />
    </div>
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <h5 class="p-2">{{ trans('Activity Logs') }}</h5>
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
      <Paginate :links="activityLogs.links" :preserveState="true" :preserveScroll="true" />
    </div>
  </div>
</template>
