<script setup>
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import ChartSkeleton from '@/Components/Dashboard/Skeleton/Chart.vue'
import OverviewSkeleton from '@/Components/Dashboard/Skeleton/OverviewCard.vue'
import DetailCardSkeleton from '@/Components/Dashboard/Skeleton/DetailCard.vue'
import VueApexCharts from 'vue3-apexcharts'
import { onMounted } from 'vue'

import { useAdminDashboardStore } from '@/Store/adminDashboardStore'
import { storeToRefs } from 'pinia'
defineOptions({ layout: AdminLayout })

const dashboardStore = useAdminDashboardStore()
const { textExcerpt, formatCurrency } = sharedComposable()
const {
  analytics,
  growthChart,
  campaignChart,
  salesChart,
  dateFilterQueries,
  filterForm,
  chartColors,
  loading,
  mostOrderedPlan,
  plansWithPercentage
} = storeToRefs(dashboardStore)
onMounted(() => {
  dashboardStore.fetchAnalytics('all', 'month')
})
const props = defineProps(['popularPlans', 'recentCreditLogs', 'isWaServerActive'])

const mostSorts = [
  { label: 'Today', value: 'today' },
  { label: 'Month', value: 'month' },
  { label: 'All', value: '' }
]
</script>

<template>
  <div class="space-y-6">
    <OverviewSkeleton v-if="analytics.overviews.loading" :skeleton-count="8" />
    <section v-else class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-4">
      <template v-for="(item, index) in analytics.overviews.data" :key="index">
        <div class="card rounded-2xl">
          <div class="flex items-center gap-3 p-5">
            <div
              class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-opacity-10"
              :class="item.style"
            >
              <Icon :icon="item.icon" class="text-2xl" />
            </div>
            <div class="flex flex-1 flex-col">
              <p class="text-sm tracking-wide text-slate-500">
                {{ item.title }}
              </p>
              <h5>{{ item.value }}</h5>
            </div>
          </div>
        </div>
      </template>
    </section>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
      <ChartSkeleton v-if="analytics.customers.loading" />
      <div class="card rounded-2xl p-5" v-else>
        <div class="flex flex-wrap justify-between gap-2">
          <h6>{{ trans('Customer Statistic') }}</h6>
          <select
            v-model="filterForm.customers"
            @change="
              dashboardStore.fetchAnalytics({ type: 'customers', filter: filterForm.customers })
            "
            class="select select-xl w-full capitalize md:w-40"
          >
            <option value="" selected>{{ trans('Filter By') }}</option>
            <option
              :value="item"
              v-for="item in dateFilterQueries"
              :key="item"
              :selected="filterForm.sales === item"
            >
              {{ item }}
            </option>
          </select>
        </div>
        <VueApexCharts
          v-if="$el"
          height="350"
          :options="growthChart.chartOptions"
          :series="growthChart.series"
        />
      </div>

      <ChartSkeleton v-if="analytics.campaigns.loading" />
      <div class="card rounded-2xl p-5" v-else>
        <div class="flex flex-wrap justify-between gap-2">
          <h6>{{ trans('Campaign Statistic') }}</h6>
          <select
            v-model="filterForm.campaigns"
            @change="
              dashboardStore.fetchAnalytics({ type: 'campaigns', filter: filterForm.campaigns })
            "
            class="select select-xl w-full capitalize md:w-40"
          >
            <option value="" selected>{{ trans('Filter By') }}</option>
            <option
              :value="item"
              v-for="item in dateFilterQueries"
              :key="item"
              :selected="filterForm.sales === item"
            >
              {{ item }}
            </option>
          </select>
        </div>
        <VueApexCharts
          v-if="$el"
          height="350"
          :options="campaignChart.chartOptions"
          :series="campaignChart.series"
        />
      </div>
    </div>
    <!-- Sales Chart  -->
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
      <div class="order-2 col-span-1 md:col-span-2 xl:order-3">
        <ChartSkeleton v-if="analytics.sales.loading" />
        <div class="card-body card flex h-full flex-col justify-between gap-4 rounded-2xl" v-else>
          <div class="flex flex-wrap justify-between gap-2">
            <h6>{{ trans('Overview Of Sales') }}</h6>
            <select
              v-model="filterForm.sales"
              @change="dashboardStore.fetchAnalytics({ type: 'sales', filter: filterForm.sales })"
              class="select select-xl w-full capitalize md:w-40"
            >
              <option value="" selected>{{ trans('Filter By') }}</option>
              <option
                :value="item"
                v-for="item in ['day', 'week', 'month', 'year']"
                :key="item"
                :selected="filterForm.sales === item"
              >
                {{ item }}
              </option>
            </select>
          </div>
          <div>
            <!-- Sales Location Chart  -->
            <VueApexCharts
              v-if="$el"
              type="area"
              height="400"
              :options="salesChart.chartOptions"
              :series="salesChart.series"
              key="salesChart"
            />
          </div>
        </div>
      </div>

      <!-- Most Ordered Plan  -->
      <div class="order-4 col-span-full space-y-6 xl:col-span-1">
        <div class="card card-body flex items-center justify-between gap-2 rounded-2xl">
          {{ trans('Whatsapp Server Status') }}:
          <div v-if="isWaServerActive" class="flex items-center gap-2">
            <span class="relative flex size-4 items-center justify-center">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success-400 opacity-75"
              ></span>
              <span class="relative inline-flex size-3 rounded-full bg-success-600"></span>
            </span>
            <span class="badge badge-success">
              {{ trans('Connected') }}
            </span>
          </div>
          <div v-else class="flex items-center gap-2">
            <span class="relative flex size-4 items-center justify-center">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-danger-400 opacity-75"
              ></span>
              <span class="relative inline-flex size-3 rounded-full bg-danger-600"></span>
            </span>
            <span class="badge badge-danger">
              {{ trans('Disconnected') }}
            </span>
          </div>
        </div>

        <template v-if="analytics.popularPlans.loading">
          <DetailCardSkeleton />
          <DetailCardSkeleton />
        </template>

        <div class="card card-body rounded-2xl" v-else>
          <h6>{{ trans('Popular Plans') }}</h6>
          <div class="styled-scrollbar mt-3 max-h-[21rem] space-y-3 overflow-y-auto">
            <div v-for="(plan, i) in plansWithPercentage" :key="plan.name">
              <div class="mb-1 flex justify-between">
                <span class="font-medium">{{ plan.name }}</span>
              </div>
              <div class="h-2 w-full rounded-lg bg-gray-200" :title="Math.round(plan.percentage)">
                <div
                  class="h-full rounded-lg transition-all"
                  :style="{
                    width: plan.percentage + '%',
                    backgroundColor: chartColors[i % chartColors.length]
                  }"
                ></div>
              </div>
            </div>
            <div v-for="(plan, i) in plansWithPercentage" :key="plan.name">
              <div class="mb-1 flex justify-between">
                <span class="font-medium">{{ plan.name }}</span>
              </div>
              <div class="h-2 w-full rounded-lg bg-gray-200" :title="Math.round(plan.percentage)">
                <div
                  class="h-full rounded-lg transition-all"
                  :style="{
                    width: plan.percentage + '%',
                    backgroundColor: chartColors[i % chartColors.length]
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Store Analytics, Active Users, Sales By Location, Top & Most Viewed Product Section End  -->

    <DetailCardSkeleton v-if="analytics.popularPlans.loading" />
    <section class="grid grid-cols-12 place-items-start gap-6" v-else>
      <div class="card-body card col-span-8 space-y-2 rounded-2xl">
        <h6>{{ trans('Popular Plans') }}</h6>
        <!-- Seller Table  -->
        <div
          v-if="analytics.popularPlans.data?.length > 0"
          class="table-responsive whitespace-nowrap rounded-primary"
        >
          <table class="table min-w-[43rem]">
            <thead>
              <tr>
                <th>{{ trans('Plan') }}</th>
                <th>{{ trans('Active Users') }}</th>
                <th>{{ trans('Sales') }}</th>
                <th>{{ trans('Total Amount') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="plan in analytics.popularPlans.data" :key="plan.id">
                <td class="whitespace-nowrap">
                  <Link :href="route('admin.plan.index')">
                    {{ plan.name }}
                  </Link>
                </td>
                <td class="whitespace-nowrap">{{ plan.activeuser }}</td>
                <td class="whitespace-nowrap">
                  <p>{{ plan.orders_count }}</p>
                </td>
                <td>{{ plan.total_amount }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <NoDataFound v-else />
      </div>

      <div class="card-body card col-span-4 flex h-full flex-col rounded-2xl">
        <!-- Header  -->
        <h6>{{ trans('Recent Orders') }}</h6>

        <div
          v-if="analytics.recentOrders.data?.length > 0"
          class="mt-auto divide-y dark:divide-slate-600"
        >
          <template v-for="order in analytics.recentOrders.data" :key="order.id">
            <Link :href="route('admin.order.index')">
              <div class="flex items-center gap-4 py-2">
                <div class="flex h-12 w-12 min-w-12 items-center justify-center">
                  <img
                    :src="order.avatar"
                    v-lazy="
                      order.avatar == null
                        ? `https://ui-avatars.com/api/?name=${order?.name}`
                        : `${order.avatar}`
                    "
                    class="rounded-primary"
                    alt="avatar"
                  />
                </div>
                <div class="flex w-full items-center justify-between">
                  <div>
                    <h6 class="text-sm font-medium text-slate-600 dark:text-slate-300">
                      {{ order.invoice }}
                    </h6>
                    <p class="text-sm text-slate-400">{{ order.plan }}</p>
                    <p class="text-xs text-slate-200">By {{ order.name }}</p>
                  </div>

                  <div>
                    <h6 class="text-sm font-medium text-slate-600 dark:text-slate-300">
                      {{ order.amount }}
                    </h6>
                    <p class="text-sm text-slate-400">{{ order.created_at }}</p>
                  </div>
                </div>
              </div>
            </Link>
          </template>
        </div>
        <NoDataFound v-else />
      </div>
    </section>
    <!-- Top Sellers Section End  -->

    <!-- Recent credits -->
    <section class="grid grid-cols-3 place-items-start gap-6">
      <div class="card-body card col-span-2 rounded-2xl">
        <h6>{{ trans('Recent Credits') }}</h6>

        <!-- jobs Table  -->
        <div class="table-responsive whitespace-nowrap rounded-primary">
          <table class="table">
            <thead>
              <tr>
                <th>{{ trans('Invoice') }}</th>
                <th>{{ trans('User Name') }}</th>
                <th>{{ trans('Credits') }}</th>

                <th>
                  {{ trans('Price') }}
                </th>
                <th>
                  {{ trans('Status') }}
                </th>
                <th>
                  {{ trans('Gateway') }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="creditLog in recentCreditLogs" :key="creditLog.id">
                <td>
                  <p class="text-primary-500">{{ creditLog.invoice_no }}</p>
                </td>
                <td>
                  <Link :href="route('admin.users.show', creditLog.user_id)">
                    {{ textExcerpt(creditLog.user.name, 30) }}
                  </Link>
                </td>
                <td>
                  {{ creditLog.credits }}
                </td>
                <td>
                  {{ formatCurrency(creditLog.price) }}
                </td>
                <td>
                  <span
                    :class="creditLog.status == 1 ? 'badge badge-success' : 'badge badge-warning'"
                  >
                    {{ creditLog.status == 1 ? 'Complete' : 'Pending' }}
                  </span>
                </td>
                <td>
                  {{ creditLog.gateway.name }}
                </td>
              </tr>
            </tbody>
            <NoDataFound v-if="recentCreditLogs.length == 0" :for-table="true" />
          </table>
        </div>
      </div>

      <div class="card-body card col-span-1 w-full rounded-2xl">
        <div class="flex flex-wrap items-center justify-between">
          <h6>{{ trans('Most Ordered Plan') }}</h6>
          <div class="flex items-center gap-2">
            <template v-for="(sort, i) in mostSorts" :key="sort.label">
              <button
                type="button"
                @click="
                  () => {
                    filterForm.plan = sort.value
                    dashboardStore.fetchAnalytics({
                      type: 'mostOrderedPlan',
                      filter: filterForm.plan
                    })
                  }
                "
              >
                <span
                  class="text-xs capitalize"
                  :class="{
                    'font-medium text-primary-500': filterForm.plan == sort.value
                  }"
                >
                  {{ trans(sort.label) }}
                </span>
              </button>
              <span v-if="i < 2" class="text-sm text-slate-200 dark:text-slate-600">|</span>
            </template>
          </div>
        </div>
        <DetailCardSkeleton classes="m-0 h-24 p-0" v-if="analytics.mostOrderedPlan.loading" />
        <div
          v-else-if="Object.keys(mostOrderedPlan)?.length > 0 && !analytics.mostOrderedPlan.loading"
          class="mt-4 flex items-center gap-4 rounded-primary bg-slate-50 p-4 dark:bg-slate-900"
        >
          <Link :href="route('admin.plan.index')">
            <div class="flex flex-1 flex-col gap-1">
              <h3 class="text-sm font-semibold">{{ mostOrderedPlan.title }}</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ trans('Price') }}: {{ formatCurrency(mostOrderedPlan.price) }},
                {{ mostOrderedPlan.days == 30 ? 'Monthly' : 'Yearly' }}, {{ trans('Total Order') }}:
                {{ mostOrderedPlan.orders_count }}
              </p>
            </div>
          </Link>
        </div>
        <NoDataFound v-else />
      </div>
    </section>
  </div>
</template>
