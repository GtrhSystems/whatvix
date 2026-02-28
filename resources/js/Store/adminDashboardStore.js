import { defineStore } from 'pinia'
import axios from 'axios'
import { computed, ref } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'

const { moduleLabel, pickBy } = sharedComposable()

export const useAdminDashboardStore = defineStore('adminDashboard', () => {
  const error = ref(null)
  const filterForm = ref({
    campaigns: 'month',
    customers: 'month',
    messages: 'month',
    plan: '',
    sales: 'month'
  })
  const chartColors = ref([
    '#66618e',
    '#42b6c9',
    '#9575cd',
    '#f64f59',
    '#1bc943',
    '#36a2eb',
    '#ff6384',
    '#ff9f40'
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
    mostOrderedPlan: {
      data: {},
      loading: false
    },
    recentOrders: {
      data: [],
      loading: false
    },
    sales: {
      data: [],
      loading: false
    },
    popularPlans: {
      data: [],
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
  async function fetchAnalytics({ type = 'all', filter = null, platform = null }) {
    setLoadingState(true, type)
    error.value = null

    try {
      const params = pickBy({
        analytics: type,
        filter,
        platform
      })

      const response = await axios.get('/api/admin/dashboard', { params })
      const { data } = response

      const stateUpdates = {
        all: () => {
          analytics.value.customers.data = data.customers
          analytics.value.campaigns.data = data.campaigns
          analytics.value.messages.data = data.messages
          analytics.value.overviews.data = data.overviews
          analytics.value.mostOrderedPlan.data = data.most_ordered_plan
          analytics.value.recentOrders.data = data.recent_orders
          analytics.value.popularPlans.data = data.popular_plans
          analytics.value.sales.data = data.sales
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
        mostOrderedPlan: () => {
          analytics.value[type].data = data.most_ordered_plan
        },
        recentOrders: () => {
          analytics.value[type].data = data.recent_orders
        },
        sales: () => {
          analytics.value[type].data = data.sales
        }
      }

      const updateFn = stateUpdates[type]
      if (updateFn) updateFn()
      setTimeout(() => {
        setLoadingState(false, type)
      }, 1000)
      return data
    } catch (err) {
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
          height: 350,
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
  const salesChart = computed(() => {
    return {
      series: [
        {
          name: 'Sales',
          data: analytics.value.sales.data.map((item) => {
            const sales = Number(item.sales)

            return isNaN(sales) ? 0 : sales
          })
        }
      ],
      chartOptions: {
        colors: chartColors.value,
        chart: {
          type: 'area',
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth'
        },
        xaxis: {
          type: 'category',
          categories: analytics.value.sales.data.map((item) => String(item.date))
        }
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
          height: 350,
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

  const plansWithPercentage = computed(() => {
    const allPlans = analytics.value.popularPlans.data || []

    const totalSales = allPlans.reduce((sum, plan) => sum + Number(plan.orders_count), 0)
    return allPlans.map((plan) => {
      const percentage = totalSales ? (Number(plan.orders_count) / totalSales) * 100 : 0
      return {
        ...plan,
        percentage: percentage
      }
    })
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
    mostOrderedPlan: computed(() => analytics.value.mostOrderedPlan.data),
    // Actions
    fetchAnalytics,

    // Computed
    growthChart,
    campaignChart,
    messageChart,
    salesChart,
    analyticsData,
    plansWithPercentage
  }
})
