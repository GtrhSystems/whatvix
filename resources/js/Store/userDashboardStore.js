import { defineStore } from 'pinia'
import axios from 'axios'
import moment from 'moment'
import { computed, ref } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'

const { moduleLabel, pickBy } = sharedComposable()

export const useUserDashboardStore = defineStore('userDashboard', () => {
  // State

  const error = ref(null)
  const filterForm = ref({
    campaigns: 'month',
    customers: 'month',
    messages: 'month'
  })
  const chartColors = ref([
    '#3c355b',
    '#2a8f9e',
    '#8b8c99',
    '#6b56a3',
    '#c0392b',
    '#138d29',
    '#2b7ca9'
  ])
  const analytics = ref({
    customers: {
      data: [],
      loading: false
    },
    campaigns: {
      data: [],
      loading: false
    },
    messages: {
      data: [],
      loading: false
    },
    overviews: {
      data: [],
      loading: false
    },
    schedule_campaigns: {
      data: [],
      loading: false
    },
    storage: {
      data: 0,
      loading: false
    },
    plan_data: {
      data: 0,
      loading: false
    }
  })

  // Actions
  function setLoadingState(state = true, type = 'all') {
    if (type === 'all') {
      Object.keys(analytics.value).forEach((key) => {
        analytics.value[key].loading = state
      })
    } else {
      analytics.value[type].loading = state
    }
  }
  async function fetchAnalytics({ type = 'all', filter = null, platform = null, user_id = null }) {
    setLoadingState(true, type)
    error.value = null

    try {
      const params = pickBy({
        analytics: type,
        filter,
        platform,
        user_id
      })

      const response = await axios.get('/api/user/dashboard', { params })
      const { data } = response

      const stateUpdates = {
        all: () => {
          analytics.value.customers.data = data.customers
          analytics.value.campaigns.data = data.campaigns
          analytics.value.messages.data = data.messages
          analytics.value.overviews.data = data.overviews
        },
        customers: () => {
          analytics.value[type].data = data.customers
        },
        campaigns: () => {
          analytics.value[type].data = data.campaigns
        },
        messages: () => {
          analytics.value[type].data = data.messages
        },
        overviews: () => {
          analytics.value[type].data = data.overviews
        },
        schedule_campaigns: () => {
          analytics.value[type].data = data.schedule_campaigns
        },
        storage: () => {
          analytics.value[type].data = data.storage
        },
        plan_data: () => {
          analytics.value[type].data = data.plan_data
        }
      }

      const updateFn = stateUpdates[type]
      if (updateFn) updateFn()
      setLoadingState(false, type)
      return data
    } catch (err) {
      setLoadingState(false, type)
      error.value = `Failed to fetch ${type} analytics: ${err.message}`
      throw err
    }
  }

  function transformChartData(data) {
    const dates = [
      ...new Set(
        Object.values(data).flatMap((platformData) => platformData.map((item) => item.date))
      )
    ].sort((a, b) => new Date(a) - new Date(b))

    const series = Object.entries(data).map(([platform, platformData]) => {
      const dataMap = new Map(platformData.map((item) => [item.date, item.total]))

      return {
        name: moduleLabel(platform),
        data: dates.map((date) => dataMap.get(date) || 0)
      }
    })

    return { series, dates }
  }
  function convertToBytes(value) {
    const match = value.match(/([\d.]+)\s*(GB|MB|KB|B)/i)
    if (!match) throw new Error("Invalid format. Use a format like '2.00 GB'.")

    const size = parseFloat(match[1])
    const unit = match[2].toUpperCase()

    const units = {
      B: 1,
      KB: 1024,
      MB: 1024 ** 2,
      GB: 1024 ** 3
    }

    return Math.round(size * units[unit])
  }
  // Computed
  const analyticsData = computed(() => {
    return Object.keys(analytics.value).map((key) => {
      return {
        [key]: analytics.value[key].data
      }
    })
  })
  const loading = computed(() => {
    return Object.values(analytics.value).every((item) => item.loading)
  })
  const growthChart = computed(() => {
    const { series, dates } = transformChartData(analytics.value.customers.data)

    return {
      series: series,
      chartOptions: {
        colors: chartColors.value,
        chart: {
          height: 400,
          type: 'area',
          toolbar: { show: false },
          zoom: { enabled: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: {
          type: 'string',
          categories: dates
        },
        yaxis: {
          show: false,
          opposite: true,
          labels: {
            formatter: function (val) {
              return val.toFixed(0)
            }
          }
        }
      }
    }
  })
  const storageUsage = computed(() => {
    const data = analytics.value.storage?.data
    const userMaxStorage = analytics.value.plan_data?.data?.storage?.value || 1
    const usage = data ? convertToBytes(data) : 0
    const maxStorage = convertToBytes(userMaxStorage + '.00 GB')
    return Math.round((usage / maxStorage) * 100)
  })
  const storageChart = computed(() => {
    const userMaxStorage = analytics.value.plan_data?.data?.storage?.value || '0.00 GB'
    const data = `${analytics.value.storage?.data} / ${
      userMaxStorage === -1 ? 'Unlimited' : userMaxStorage + ' GB'
    }`

    return {
      series: [storageUsage.value],
      chartOptions: {
        chart: {
          height: 300,
          type: 'radialBar',
          offsetY: 30,
          toolbar: { show: false }
        },
        plotOptions: {
          radialBar: {
            startAngle: -90,
            endAngle: 90,
            hollow: { size: '60%' },
            track: {
              background: '#9ca3af',
              strokeWidth: '100%',
              margin: 15
            },
            dataLabels: {
              name: {
                show: true,
                offsetY: -20,
                fontSize: '13px',
                fontWeight: '500',
                color: '#9ca3af'
              },
              value: {
                offsetY: -10,
                fontSize: '14px',
                fontWeight: 'bold',
                color: '#9ca3af',
                formatter: () => data ?? 'Loading...'
              }
            }
          }
        },
        colors: ['#6E43D4'],
        fill: {
          type: 'solid',
          colors: ['#1daa61'] // Solid fill color
        },
        stroke: { lineCap: 'round' },
        labels: ['Storage Usage']
      }
    }
  })

  const campaignChart = computed(() => {
    const { series, dates } = transformChartData(analytics.value.campaigns?.data)
    return {
      series: series,
      chartOptions: {
        colors: chartColors.value,
        chart: {
          height: 400,
          type: 'bar',
          toolbar: { show: false },
          zoom: { enabled: false }
        },
        plotOptions: {
          bar: {
            borderRadius: 3,
            columnWidth: '12px'
          }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: {
          type: 'string',
          categories: dates
        },
        yaxis: {
          show: false,
          opposite: true,
          labels: {
            formatter: function (val) {
              return val.toFixed(0)
            }
          }
        }
      }
    }
  })

  const messageChart = computed(() => {
    const { series, dates } = transformChartData(analytics.value.messages?.data)
    return {
      series: series,
      chartOptions: {
        colors: chartColors.value,
        chart: {
          height: 400,
          type: 'bar',
          toolbar: { show: false },
          zoom: { enabled: false }
        },
        plotOptions: {
          bar: {
            borderRadius: 3,
            columnWidth: '12px'
          }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: {
          type: 'string',
          categories: dates
        },
        yaxis: {
          show: false,
          opposite: true,
          labels: {
            formatter: function (val) {
              return val.toFixed(0)
            }
          }
        }
      }
    }
  })

  const pieChart = computed(() => {
    const data = Object.entries(analytics.value.messages?.data).map(([platform, platformData]) => {
      const total = platformData.reduce((sum, item) => sum + item.total, 0)
      return {
        name: moduleLabel(platform),
        value: total
      }
    })
    return {
      series: data.map((item) => item.value),
      chartOptions: {
        chart: { type: 'donut' },
        colors: chartColors.value,
        height: 160,
        labels: data.map((item) => item.name),
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: { width: 200 },
              legend: { position: 'bottom' }
            }
          }
        ]
      }
    }
  })

  const calculatePlatformPercentages = computed(() => {
    const platformTotals = Object.entries(analytics.value.messages?.data).map(
      ([platform, platformData]) => ({
        name: platform,
        value: platformData.reduce((sum, item) => sum + item.total, 0)
      })
    )

    const totalSum = platformTotals.reduce((sum, item) => sum + item.value, 0)

    return platformTotals.map((item) => ({
      name: moduleLabel(item.name),
      value: totalSum > 0 ? Number(((item.value / totalSum) * 100).toFixed(2)) : 0
    }))
  })

  const currentWeekDays = computed(() => {
    const days = []
    const today = new Date()
    for (let i = 0; i < 7; i++) {
      days.push(moment(today).add(i, 'days').format('D'))
    }
    return days
  })

  const dateFilterQueries = ref(['day', 'week', 'month', 'year'])

  return {
    // State
    analytics,
    error,
    filterForm,
    dateFilterQueries,
    chartColors,
    loading,
    // Actions
    fetchAnalytics,

    // Computed
    growthChart,
    campaignChart,
    messageChart,
    pieChart,
    storageChart,
    calculatePlatformPercentages,
    currentWeekDays,
    analyticsData
  }
})
