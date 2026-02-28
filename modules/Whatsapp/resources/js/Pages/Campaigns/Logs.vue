<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'
import VueApexCharts from 'vue3-apexcharts'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { computed } from 'vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['logs', 'campaign'])
const { deleteRow } = sharedComposable()
const { failed_messages, delivered_messages, sent_messages, read_messages } = props.campaign
const chartColors = ['#F44336', '#4CAF50', '#2196F3', '#FF9800']
const campaignPieChart = computed(() => {
  return {
    series: [failed_messages, delivered_messages, sent_messages, read_messages],
    chartOptions: {
      plotOptions: {
        pie: {
          donut: {
            size: '75%'
          }
        }
      },
      chart: { type: 'donut' },
      colors: chartColors,
      height: 160,
      labels: [
        `Failed ${failed_messages}`,
        `Delivered ${delivered_messages}`,
        `Sent ${sent_messages}`,
        `Read ${read_messages}`
      ],
      responsive: [
        {
          breakpoint: 480,
          options: {
            chart: { width: 150 },
            legend: { position: 'bottom' }
          }
        }
      ]
    }
  }
})

const logsWithPercentage = computed(() => {
  const totalMessages = props.campaign.total_messages
  const formatPercentage = (value) => (value ? (value / totalMessages) * 100 : 0)
  return [
    {
      name: 'Failed',
      value: props.campaign.failed_messages,
      percentage: formatPercentage(props.campaign.failed_messages)
    },
    {
      name: 'Delivered',
      value: props.campaign.delivered_messages,
      percentage: formatPercentage(props.campaign.delivered_messages)
    },
    {
      name: 'Sent',
      value: props.campaign.sent_messages,
      percentage: formatPercentage(props.campaign.sent_messages)
    },
    {
      name: 'Read',
      value: props.campaign.read_messages,
      percentage: formatPercentage(props.campaign.read_messages)
    }
  ]
})
</script>

<template>
  <div class="grid grid-cols-1 gap-y-4 md:gap-4 lg:grid-cols-3">
    <div class="table-responsive col-span-2 w-full rounded-2xl">
      <table class="table">
        <thead>
          <tr>
            <th>
              {{ trans('Phone Number') }}
            </th>
            <th>
              {{ trans('Sent') }}
            </th>
            <th>
              {{ trans('Delivered') }}
            </th>
            <th class="!text-right">
              {{ trans('Read') }}
            </th>
          </tr>
        </thead>
        <tbody class="tbody" v-if="logs.data.length">
          <tr v-for="(log, index) in logs.data" :key="index">
            <td>
              <a
                :href="`https://wa.me/${log.meta?.phone}`"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:underline"
              >
                {{ log.meta?.phone }}
              </a>
            </td>
            <td>
              <i
                v-if="log.failed_at"
                class="bx"
                :class="log.failed_at ? 'bxs-info-circle text-red-500' : 'bx-info-circle'"
                :title="log.meta.errors?.[0]?.error_data?.details ?? log.failed_at"
              ></i>
              <i
                v-else
                class="bx"
                :class="log.send_at ? 'bxs-check-circle text-green-500' : 'bx-circle'"
                :title="log.send_at"
              ></i>
            </td>
            <td>
              <i
                class="bx"
                :class="log.delivered_at ? 'bxs-check-circle text-green-500' : 'bx-circle'"
                :title="log.delivered_at"
              ></i>
            </td>
            <td>
              <div class="text-end">
                <i
                  class="bx"
                  :class="log.read_at ? 'bxs-check-circle text-green-500' : 'bx-circle'"
                  :title="log.read_at"
                ></i>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound :forTable="true" v-else />
      </table>
      <div class="w-full">
        <Paginate v-if="logs.data.length" :links="logs.links" />
      </div>
    </div>
    <div class="col-span-full space-y-4 lg:col-span-1">
      <div class="card card-body flex h-56 flex-col items-center justify-center rounded-2xl">
        <VueApexCharts
          height="200"
          :options="campaignPieChart.chartOptions"
          :series="campaignPieChart.series"
        />
      </div>
      <div class="card card-body rounded-2xl">
        <h6>{{ trans('Logs') }}</h6>
        <div class="mt-4 space-y-3">
          <div v-for="(log, i) in logsWithPercentage" :key="i">
            <p class="mb-1 text-sm">{{ log.name }}</p>

            <div class="h-1.5 w-full rounded-lg bg-gray-200">
              <div
                class="h-full rounded-lg transition-all"
                :style="{ width: log.percentage + '%', backgroundColor: chartColors[i] }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
