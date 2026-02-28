<script setup>
import { ref } from 'vue'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
const { formatCurrency } = sharedComposable()
const props = defineProps(['plan', 'limit'])
const isExpanded = ref(false)

const makeExpanded = () => {
  isExpanded.value = true
}

const makeCollapsed = () => {
  isExpanded.value = false
}

let totalItemsCount = Object.keys(props.plan.data || {}).length

const listItemStyle = (index) => {
  let opacity = 1
  if (isExpanded.value || props.limit >= totalItemsCount) {
    return { opacity }
  }

  if (totalItemsCount > 5 && index > 5) {
    opacity = (10 - index) / 5
  }

  return { opacity }
}
</script>

<template>
  <div class="row" @mouseleave="makeCollapsed">
    <div class="col-lg-6">
      <div class="pr-header mb-30">
        <div class="plan-duration">{{ plan.duration }}</div>
        <div class="price">{{ formatCurrency(plan.price) }}</div>
        <div class="info1 fw-500 fs-20">
          {{ plan.description }}
        </div>
      </div>
      <!-- /.pr-header -->
    </div>

    <div class="col-lg-6">
      <div
        @mouseenter="makeExpanded"
        class="overflow-hidden"
        :style="{ height: isExpanded ? 'auto' : `${limit * 4}rem` }"
      >
        <ul class="style-none package-feature">
          <li v-for="(data, key, index) in plan.data" :key="key" :style="listItemStyle(index)">
            {{ data.overview }}
          </li>
        </ul>
      </div>
      <a :href="route('register', { plan_id: plan.id })" class="btn-nineteen mt-30">{{
        trans('Choose Plan')
      }}</a>
    </div>
  </div>
</template>
