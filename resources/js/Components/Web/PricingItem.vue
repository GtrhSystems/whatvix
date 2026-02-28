<script setup>
import { ref } from 'vue'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
const { formatCurrency } = sharedComposable()
const props = defineProps(['plan'])
</script>

<template>
  <div class="pr-column w-100 mt-30">
    <div class="pr-header mb-30">
      <div class="d-flex align-items-center">
        <div class="price">
          {{ formatCurrency(plan.price, '$', 0) }}
        </div>
        <div class="info1">{{ plan.short_description }}</div>
      </div>
      <div class="plan-name">{{ plan.title }}</div>
      <p class="info2">
        {{ plan.description }}
      </p>
    </div>
    <!-- /.pr-header -->
    <a
      :href="route('register', { plan_id: plan.id })"
      class="trial-btn w-100 d-flex justify-content-between align-items-center"
    >
      {{
        plan.is_trial ? trans('Free Trial For ' + plan.trial_days + ' Days') : trans('Get Started')
      }}
      <span class="icon d-flex align-items-center justify-content-center"
        ><i class="fa-solid fa-chevron-right"></i></span
    ></a>
    <ul @mouseenter="makeExpanded" class="style-none package-feature lg-mt-50 mt-60">
      <li v-for="(data, key) in plan.data" :key="key" :class="{ none: data.value === false }">
        {{ data.overview }}
      </li>
    </ul>
  </div>
</template>
