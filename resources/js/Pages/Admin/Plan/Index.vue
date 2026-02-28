<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import { useForm } from '@inertiajs/vue3'
import toast from '@/Composables/toastComposable'

defineOptions({ layout: AdminLayout })

const props = defineProps(['plans', 'planSetting'])
const { deleteRow } = sharedComposable()

const form = useForm({})
const syncUserPlan = (plan) => {
  if (plan.activeuser_count < 1) {
    toast.danger('No active user found in this plan')
    return
  }
  form.patch(route('admin.plan.sync', plan.id))
}
</script>

<template>
  <div class="space-y-6">
    <div v-if="plans.length != 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="card overflow-hidden rounded-lg shadow-md hover:shadow-lg"
      >
        <div class="card-header border-b p-4 text-center">
          <h4 class="mb-1 text-xl font-semibold">{{ plan.title }}</h4>
          <div class="price-container mb-2">
            <h3 class="text-2xl font-bold text-primary-500">{{ plan.price_format }}</h3>
            <span class="text-sm">
              {{
                plan.days === 30
                  ? trans('Per month')
                  : plan.days === 365
                  ? trans('Per year')
                  : trans('Lifetime')
              }}
            </span>
          </div>
          <div class="inline-block rounded-full px-3 py-1 text-sm">
            {{ trans('Active Users') }}: {{ plan.activeuser_count }}
          </div>
        </div>

        <div class="card-body space-y-3 p-4">
          <template v-for="(planData, key) in plan.data" :key="key">
            <div class="flex items-center justify-between gap-x-7">
              <div class="flex items-center gap-2">
                <span
                  class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full"
                  :class="planData.value ? 'bg-green-100' : 'bg-red-100'"
                >
                  <Icon v-if="planData.value" icon="fa:check-circle" class="text-green-600" />
                  <Icon v-else icon="fa:times-circle" class="text-red-600" />
                </span>
                <span class="capitalize text-slate-600 dark:text-slate-300">
                  {{ key.replace(/_/g, ' ') }}
                </span>
              </div>
              <span
                class="max-w-xs text-end text-sm font-medium text-slate-600 dark:text-slate-400"
              >
                {{
                  planData.value == '-1'
                    ? 'Unlimited'
                    : planData.value === true || planData.value === false
                    ? ''
                    : Array.isArray(planData.value)
                    ? planData.value.join(', ')
                    : `${planData.value}`
                }}
              </span>
            </div>
          </template>
        </div>

        <div class="border-t p-4 dark:border-gray-700">
          <div class="mb-3 flex gap-2">
            <Link
              :href="route('admin.plan.edit', plan.id)"
              class="btn btn-primary flex flex-1 items-center justify-center gap-1 rounded-md py-2"
            >
              <Icon icon="fe:pencil" size="16" />
              <span>{{ trans('Edit') }}</span>
            </Link>

            <button
              @click="
                plan.activeuser_count == 0
                  ? deleteRow(route('admin.plan.destroy', plan.id))
                  : toast.danger('You cant delete this plan, this plan has active users')
              "
              type="button"
              :disabled="plan.activeuser_count > 0"
              class="btn btn-danger flex flex-1 items-center justify-center gap-1 rounded-md py-2"
              :class="{ 'cursor-not-allowed opacity-50': plan.activeuser_count > 0 }"
            >
              <Icon icon="fe:trash" size="16" />
              <span>{{ trans('Delete') }}</span>
            </button>
          </div>
          <button
            type="button"
            @click="syncUserPlan(plan)"
            class="btn btn-success flex w-full items-center justify-center gap-1 rounded-md py-2"
            :disabled="form.processing || plan.activeuser_count == 0"
            :class="{
              'cursor-not-allowed opacity-50': form.processing || plan.activeuser_count == 0
            }"
          >
            <Icon icon="fe:sync" :class="{ 'animate-spin': form.processing }" size="16" />
            <span>{{ trans('Sync Users') }}</span>
          </button>
        </div>
      </div>
    </div>

    <NoDataFound v-else />
  </div>
</template>
