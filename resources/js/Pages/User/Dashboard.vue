<script setup>
import { computed, onMounted, ref } from 'vue'
import momentTimezone from 'moment-timezone'
import VueApexCharts from 'vue3-apexcharts'
import OverviewGrid from '@/Components/Dashboard/OverviewGrid.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import ChartSkeleton from '@/Components/Dashboard/Skeleton/Chart.vue'
import OverviewSkeleton from '@/Components/Dashboard/Skeleton/OverviewCard.vue'
import DetailCardSkeleton from '@/Components/Dashboard/Skeleton/DetailCard.vue'
import CampaignCardSkeleton from '@/Components/Dashboard/Skeleton/CampaignCard.vue'
import sharedComposable from '@/Composables/sharedComposable'
import moment from 'moment'
import { useUserDashboardStore } from '@/Store/userDashboardStore'
import { storeToRefs } from 'pinia'
import { usePage } from '@inertiajs/vue3'
import trans from '@/Composables/transComposable'
defineOptions({ layout: UserLayout })
defineProps(['systemTimezone'])

const dashboardStore = useUserDashboardStore()
const { authUser, textExcerpt, moduleLabel, copyToClipboard } = sharedComposable()
const {
  analytics,
  growthChart,
  campaignChart,
  messageChart,
  pieChart,
  storageChart,
  currentWeekDays,
  dateFilterQueries,
  filterForm,
  calculatePlatformPercentages,
  chartColors,
  loading
} = storeToRefs(dashboardStore)
onMounted(() => {
  dashboardStore.fetchAnalytics({ type: 'all', filter: 'month' })
  dashboardStore.fetchAnalytics({ type: 'schedule_campaigns', filter: 'month' })
  dashboardStore.fetchAnalytics({ type: 'storage', filter: 'month' })
  dashboardStore.fetchAnalytics({ type: 'plan_data', filter: 'month' })
  fetchEmbedLoginModuleIsActive()
})

const embedLogin = ref({
  is_active: false,
  status: 'Checking Embed Login Module Status...'
})

const fetchEmbedLoginModuleIsActive = () => {
  axios.get(route('api.whatsapp.embed-login.check-module-status')).then((response) => {
    embedLogin.value = response.data
  })
}

const isCurrentWorkspaceOwner = computed(() => {
  return usePage().props.activeWorkspace?.owner_id === authUser.value.id
})
</script>

<template>
  <OverviewSkeleton v-if="analytics.overviews.loading" :skeleton-count="8" />
  <OverviewGrid :items="analytics.overviews.data" v-else />
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-full space-y-5 xl:col-span-7">
      <ChartSkeleton v-if="analytics.customers.loading" />
      <div class="card rounded-2xl p-5" v-else>
        <div class="flex flex-wrap justify-between gap-2">
          <h6>{{ trans('Audience Growth') }}</h6>
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
          height="400"
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
          height="400"
          :options="campaignChart.chartOptions"
          :series="campaignChart.series"
        />
      </div>
      <ChartSkeleton class="mt-5" v-if="analytics.messages.loading" />
      <div class="card rounded-2xl p-5">
        <div class="flex flex-wrap justify-between gap-2">
          <h6>{{ trans('Message Statistics') }}</h6>
          <select
            v-model="filterForm.messages"
            @change="
              dashboardStore.fetchAnalytics({ type: 'messages', filter: filterForm.messages })
            "
            class="select select-xl w-full capitalize md:w-40"
          >
            <option value="" selected>{{ trans('Filter By') }}</option>
            <option
              :value="item"
              v-for="item in dateFilterQueries"
              :key="item"
              :selected="filterForm.messages === item"
            >
              {{ item }}
            </option>
          </select>
        </div>
        <VueApexCharts
          v-if="$el"
          height="400"
          :options="messageChart.chartOptions"
          :series="messageChart.series"
        />
      </div>
    </div>
    <div class="col-span-full space-y-4 xl:col-span-5">
      <CampaignCardSkeleton v-if="analytics.schedule_campaigns.loading" />
      <div class="col-span-1 lg:col-span-4" v-else>
        <div class="card rounded-2xl rounded-b-none rounded-t-2xl">
          <div
            class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-700"
          >
            <div class="flex items-center">
              <div
                class="mr-4 flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-primary-400 bg-opacity-10 text-primary-500"
              >
                <Icon icon="bx:calendar-event" class="text-2xl" />
              </div>
              <div>
                <p class="mr-1 text-sm font-semibold lg:text-lg">
                  {{ moment().format('dddd, D MMMM YYYY') }}
                </p>
              </div>
            </div>
          </div>
          <div class="flex justify-around">
            <div
              v-for="item in currentWeekDays"
              :key="item"
              class="flex flex-col items-center justify-center"
            >
              <!-- if day is today -->
              <p
                class="my-2 flex h-10 w-10 items-center justify-center rounded-full"
                :class="{
                  'border border-primary-600 text-primary-600': moment().format('D') === item
                }"
              >
                {{ item }}
              </p>
            </div>
          </div>
        </div>

        <!-- scheduled posts -->
        <div class="card mt-3 rounded-2xl rounded-b-2xl rounded-t-none p-4">
          <div v-if="analytics.schedule_campaigns.data.length > 0" class="grid grid-cols-2 gap-3">
            <div
              v-for="campaign in analytics.schedule_campaigns.data"
              :key="campaign.id"
              class="flex gap-3 rounded-md bg-slate-100 p-3 dark:bg-slate-900"
            >
              <div>
                <p class="line-clamp-1 font-semibold capitalize">
                  {{ textExcerpt(campaign.name || 'Campaign Title', 30) }}
                </p>
                <p
                  class="-mt-0.5 flex items-center gap-0.5 text-[11px] text-gray-500 dark:text-gray-400"
                >
                  <Icon icon="solar:clock-circle-broken" class="mt-0.5" />
                  <span>
                    {{
                      campaign.schedule_at != null
                        ? momentTimezone
                            .tz(campaign.schedule_at, systemTimezone)
                            .tz(campaign.timezone)
                            .format('DD MMM YYYY hh:mm A')
                        : 'N/A'
                    }}</span
                  >
                  -
                  <span class="text-xs font-semibold capitalize text-green-500">
                    {{ campaign.status }}
                  </span>
                </p>
              </div>
            </div>
          </div>
          <p v-else>{{ trans('You do not have scheduled campaign for today.') }}</p>
        </div>
      </div>
      <!-- active subscription -->
      <template v-if="loading">
        <DetailCardSkeleton v-for="n in 3" :key="n" />
      </template>
      <div class="card card-body flex items-center justify-between rounded-2xl" v-if="!loading">
        <div class="flex flex-col">
          <p class="text-lg font-semibold leading-6">
            {{ trans('Active Subscription') }}
            <span v-if="analytics.plan_data.data && authUser.will_expire" class="text-sm">
              ( {{ analytics.plan_data.data?.plan_title }} )
            </span>
          </p>
          <p class="text-sm dark:text-dark-400" v-if="authUser.will_expire">
            {{ trans('Your subscription expires on') }}
            <span>
              {{ moment(authUser.will_expire).format('MMM DD, YYYY') }}
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
      <div class="card card-body flex items-center justify-between rounded-2xl" v-if="!loading">
        <div class="flex flex-col">
          <p class="text-lg font-semibold leading-6">{{ trans('Setup Whatsapp Web') }}</p>
          <p class="text-sm dark:text-dark-400">
            {{ trans('Setup Whatsapp Web to start sending messages') }}
          </p>
          <a
            class="btn btn-soft-primary mt-6 max-w-max"
            :href="route('user.whatsapp-web.platforms.index')"
          >
            <span>
              {{ trans('Setup Account') }}
            </span>
            <Icon icon="solar:arrow-right-broken" class="mt-1 size-4" />
          </a>
        </div>
        <div
          class="flex min-h-16 min-w-16 items-center justify-center rounded-full bg-primary-600/60"
        >
          <Icon icon="bxl-whatsapp" class="size-8 text-gray-100" />
        </div>
      </div>
      <div class="card card-body flex items-center justify-between rounded-2xl" v-if="!loading">
        <div class="flex flex-col">
          <p class="text-lg font-semibold leading-6">{{ trans('Setup Whatsapp Cloud') }}</p>
          <p class="text-sm dark:text-dark-400">
            {{ trans('Setup Whatsapp Cloud to start sending messages') }}
          </p>
          <a
            v-if="embedLogin.is_active"
            class="btn btn-soft-primary mt-6 max-w-max"
            href="/user/whatsapp/platforms"
          >
            <span>
              {{ trans('Setup Account') }}
            </span>
            <Icon icon="solar:arrow-right-broken" class="mt-1 size-4" />
          </a>
          <div v-else class="mt-2">
            <span class="rounded bg-red-700/15 px-2 py-1 text-danger-700">
              {{ embedLogin.status }}
            </span>
          </div>
        </div>
        <div
          class="flex min-h-16 min-w-16 items-center justify-center rounded-full bg-primary-600/60"
        >
          <Icon icon="bxl-whatsapp" class="size-8 text-gray-100" />
        </div>
      </div>
      <CampaignCardSkeleton v-if="analytics.storage.loading && !analytics.storage.data" />
      <div class="card flex h-56 flex-col items-center justify-center rounded-2xl" v-else>
        <VueApexCharts
          v-if="$el"
          width="100%"
          height="380"
          type="radialBar"
          :options="storageChart.chartOptions"
          :series="storageChart.series"
        />
      </div>
      <div class="card card-body rounded-2xl">
        <div class="flex flex-wrap justify-between gap-2">
          <h6 class="space-x-1">
            <span v-for="(item, index) in calculatePlatformPercentages" :key="item.name">
              {{ moduleLabel(item.name) }}
              <template v-if="index < calculatePlatformPercentages.length - 1">,</template>
            </span>
          </h6>
        </div>

        <div class="flex items-center justify-center pt-5">
          <VueApexCharts
            v-if="$el"
            height="160"
            type="donut"
            :options="pieChart.chartOptions"
            :series="pieChart.series"
          />
        </div>

        <div class="flex h-3 rounded-md pb-5 pt-8">
          <div
            v-for="(item, index) in calculatePlatformPercentages"
            :key="item.name"
            class="relative h-4 rounded hover:bg-opacity-20"
            :style="`width: ${item.value}%; background: ${chartColors[index]}`"
          >
            <span
              class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-xs text-dark-300"
            >
              {{ item.value }}%
            </span>
            <span class="absolute left-1/2 top-4 -translate-x-1/2 text-[10px]">{{
              item.name
            }}</span>
          </div>
        </div>
      </div>
      <div class="card card-body relative space-y-2 overflow-hidden rounded-2xl">
        <div
          class="card absolute inset-0 z-10 flex flex-col items-center justify-center rounded-md"
          v-if="!isCurrentWorkspaceOwner"
        >
          <p class="mx-auto w-9/12 px-5 py-1 text-center text-sm">
            {{ trans('Only the owner of this workspace can see the auth key') }}
          </p>
        </div>
        <h6>
          {{ trans('Auth Key') }}
          <span v-if="!isCurrentWorkspaceOwner" class="text-xs">{{
            trans('(Only Owner can see)')
          }}</span>
        </h6>
        <div class="flex items-stretch gap-2">
          <input
            type="text"
            class="input"
            placeholder="Auth key"
            :value="isCurrentWorkspaceOwner ? authUser.authkey : ''"
          />
          <button
            type="button"
            @click="copyToClipboard(isCurrentWorkspaceOwner ? authUser.authkey : '')"
            class="btn btn-soft-primary"
          >
            <Icon icon="bx:copy" />
          </button>
        </div>

        <Link
          as="button"
          :href="route('user.account-settings.regenerate-key')"
          method="post"
          class="btn btn-soft-primary w-full"
          preserve-scroll
        >
          <Icon icon="bx:refresh" />
          <span>{{ trans('Regenerate') }}</span>
        </Link>
      </div>
    </div>
  </div>
</template>
