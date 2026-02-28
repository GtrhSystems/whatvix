<script setup>
import Modal from '@/Components/Dashboard/Modal.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import VueApexCharts from 'vue3-apexcharts'
import { useModalStore } from '@/Store/modalStore'
import { storeToRefs } from 'pinia'
import sharedComposable from '@/Composables/sharedComposable'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import notify from '@/Composables/toastComposable'
import moment from 'moment'

defineOptions({ layout: UserLayout })
const modal = useModalStore()
const { getState } = storeToRefs(modal)

const props = defineProps([
  'credits',
  'costChartData',
  'activeChartFilterBtn',
  'credit_fee',
  'gateways',
  'totalCostAmount',
  'availableCreditAmount',
  'spendCreditAmount'
])

const availableAndSpendPercentage = computed(() => {
  let total = props.availableCreditAmount + props.spendCreditAmount
  return {
    available: Math.round((props.availableCreditAmount / total) * 100),
    spend: Math.round((props.spendCreditAmount / total) * 100)
  }
})

const chartFilterBtns = ['day', 'week', 'month', 'year']

const costsChart = computed(() => {
  return {
    series: [
      {
        name: 'costs',
        data: props.costChartData.map((item) => item.credit)
      }
    ],
    chartOptions: {
      colors: ['#69ae84'],
      chart: {
        height: 350,
        type: 'bar',
        toolbar: {
          show: false
        }
      },

      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '10px',
          endingShape: 'rounded'
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      yaxis: {
        show: false,
        opposite: true,
        labels: {
          formatter: function (val) {
            return val.toFixed(0)
          }
        }
      },
      xaxis: {
        type: 'string',
        categories: props.costChartData.map((item) => item.date)
      }
    }
  }
})

const pieChart = computed(() => {
  return {
    series: [props.availableCreditAmount, props.spendCreditAmount],
    chartOptions: {
      colors: ['#22c55e', '#f87171'],
      labels: ['Available', 'Used'],
      chart: {
        width: 210,
        type: 'donut'
      },
      dataLabels: {
        enabled: false
      },
      responsive: [
        {
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              show: false
            }
          }
        }
      ],
      legend: {
        show: false
      }
    }
  }
})

//credits
const { formatCurrency, authUser } = sharedComposable()
const activeGateway = ref(props.gateways[0]?.id || 0)
const credits = ref(null)
const conversionRate = ref(props.credit_fee)
const convertedAmount = computed(() => {
  return credits.value / conversionRate.value
})
const isProcessing = ref(false)
const manualPayment = ref({
  image: null,
  comment: '',
  phone: authUser.value.phone
})
const findGateway = computed(() => {
  return props.gateways.find((gateway) => gateway.id === activeGateway.value)
})
const submit = (gateway_id) => {
  if (findGateway.value.min_amount > convertedAmount.value) {
    notify.danger(
      'The minimum transaction amount is ' + formatCurrency(findGateway.value.min_amount)
    )
    return
  }
  if (findGateway.value.max_amount != -1) {
    if (findGateway.value.max_amount < convertedAmount.value) {
      notify.danger(
        'The maximum transaction amount is ' + formatCurrency(findGateway.value.max_amount)
      )
      return
    }
  }
  isProcessing.value = true
  router.post(
    route('credit.pay'),
    {
      gateway_id: gateway_id,
      total: convertedAmount.value,
      credits: credits.value,
      manualPayment: findGateway.value.is_auto == 0 ? manualPayment.value : null,
      phone: manualPayment.value.phone
    },
    {
      onFinish: () => {
        credits.value = 0
        isProcessing.value = false
      }
    }
  )
}

const onSelectChartSortScope = (url) => {
  router.get(url)
}
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <div class="card card-body col-span-full lg:col-span-7 2xl:col-span-8">
      <!-- bar chart -->
      <div class="chart-filter-area flex justify-between">
        <h6 class="flex items-center gap-2">
          <i class="bx bx-credit-card text-2xl"></i> {{ trans('Costs') }}:
          {{ totalCostAmount }}
        </h6>
        <select
          class="select w-36 capitalize"
          @input="(e) => onSelectChartSortScope(e.target.value)"
        >
          <option
            v-for="btn in chartFilterBtns"
            :key="btn"
            :selected="btn == activeChartFilterBtn"
            :value="route('user.credits.index', { scope: btn })"
          >
            {{ btn }}
          </option>
        </select>
      </div>

      <VueApexCharts
        type="bar"
        height="350"
        :options="costsChart.chartOptions"
        :series="costsChart.series"
      />
    </div>
    <div class="card card-body col-span-full lg:col-span-5 2xl:col-span-4">
      <span class="me-3 font-bold">{{ trans('Monthly Bill') }}</span>
      <span
        >{{ moment().startOf('month').format('DD MMM') }} -
        {{ moment().endOf('month').format('DD MMM') }}</span
      >
      <!-- pie chart -->
      <div class="flex h-full flex-col justify-evenly">
        <div class="grid grid-cols-2 place-items-center gap-1">
          <VueApexCharts
            type="donut"
            height="210"
            width="210"
            :options="pieChart.chartOptions"
            :series="pieChart.series"
          />
          <div class="mt-2">
            <div class="mb-3 space-x-1">
              <span class="text-lg font-bold">{{ spendCreditAmount }}</span>
              <span class="text-sm">/ {{ availableCreditAmount }} {{ trans('limit') }}</span>
            </div>
            <button type="button" @click="modal.open('addCredits')" class="btn btn-primary">
              <Icon icon="bx:plus" />
              <span>{{ trans('Increase limit‍') }}</span>
            </button>
          </div>
        </div>
        <div>
          <div class="flex gap-2">
            <div class="font-bold">{{ trans('Credit Usage') }}</div>
          </div>
          <div class="mb-1 mt-2 flex justify-between gap-3">
            <div class="flex items-center gap-1">
              <span class="h-4 w-4 rounded-sm bg-green-500"></span>
              <span>{{ trans('Available') }}</span>
            </div>
            <div class="flex items-center gap-1">
              <div class="h-4 w-4 rounded-sm bg-red-400"></div>
              <span>{{ trans('Used') }}</span>
            </div>
          </div>
          <div class="flex items-center">
            <div class="relative w-full">
              <div class="absolute left-0 h-4 w-full rounded bg-red-400"></div>
              <div
                class="absolute left-0 h-4 rounded bg-green-500"
                :style="{
                  width: availableAndSpendPercentage.available + '%'
                }"
              ></div>
            </div>
          </div>
          <div class="mt-5 flex justify-between">
            <span> {{ availableAndSpendPercentage.available }} % </span>
            <span> {{ availableAndSpendPercentage.spend }} % </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Modal state="addCredits" :header-state="true" header-title="Buy Credits">
    <p class="mb-3 text-center">
      {{ conversionRate }} {{ trans('Credits') }} = {{ formatCurrency(1) }}
    </p>

    <input
      class="input"
      step="5"
      type="number"
      v-model.number="credits"
      placeholder="Enter Credit amount"
    />
    <p class="fw-bold mt-2 text-left">
      {{ trans('Total:') }} {{ formatCurrency(convertedAmount || 0) }}
    </p>
    <div class="my-8 flex flex-wrap gap-2 px-3">
      <template v-for="gateway in gateways" :key="gateway.id">
        <button
          class="relative flex flex-1 items-center justify-center rounded-lg p-4"
          @click="activeGateway = gateway.id"
          :class="{ 'border bg-slate-50': activeGateway == gateway.id }"
        >
          <div v-show="activeGateway == gateway.id">
            <i
              class="bx bx-check absolute right-[-10px] top-[-10px] rounded-full bg-primary-600 p-1 text-white"
            ></i>
          </div>
          <img :src="gateway.logo" class="max-w-24" />
        </button>
      </template>
    </div>
    <template v-for="gateway in gateways" :key="gateway.id">
      <div v-show="activeGateway === gateway.id" class="" :id="'gateway-form' + gateway.id">
        <form method="post" @submit.prevent="submit(gateway.id)" enctype="multipart/form-data">
          <div class="table-responsive">
            <table class="table-borderless table text-sm">
              <template v-if="gateway.charge != 0">
                <tr class="border">
                  <td class="border border-black p-2">
                    {{ trans('Gateway Charge: ') }}
                  </td>
                  <td class="text-center">
                    {{ formatCurrency(gateway.charge) }}
                  </td>
                </tr>
              </template>
            </table>

            <template v-if="gateway.comment != null">
              <table class="table-borderless mt-2 table">
                <tr>
                  <td class="fw-500 text-sm">
                    {{ trans('Payment Instruction: ') }}
                  </td>
                </tr>

                <tr>
                  <td class="text-sm">
                    {{ gateway.comment }}
                  </td>
                </tr>
              </table>
            </template>
            <template v-if="gateway.phone_required == 1">
              <div class="form-group mt-2">
                <label class="fw-500 text-fs-sm my-2">{{ trans('Your phone number') }}</label>
                <input
                  type="number"
                  name="phone"
                  placeholder="Your phone number"
                  class="input"
                  required
                  v-model="manualPayment.phone"
                />
              </div>
            </template>
            <template v-if="gateway.is_auto == 0">
              <div class="form-group mt-1">
                <label class="fw-500 text-fs-sm my-2">{{
                  trans('Submit your payment proof')
                }}</label>
                <input
                  @input="
                    (e) => {
                      manualPayment.image = e.target.files[0]
                    }
                  "
                  type="file"
                  name="image"
                  class="input"
                  required
                  accept="image/*"
                />
              </div>
              <div class="form-group">
                <label class="fw-500 text-fs-sm my-2">{{ trans('Comment') }}</label>
                <textarea
                  v-model="manualPayment.comment"
                  class="input"
                  required
                  name="comment"
                  placeholder="comment"
                  maxlength="500"
                ></textarea>
              </div>
            </template>
          </div>
          <button
            :disabled="isProcessing"
            type="submit"
            class="btn btn-primary mt-4 w-full py-2 text-lg"
          >
            {{ trans('Pay Now') }}
          </button>
        </form>
      </div>
    </template>
  </Modal>
</template>
