<script setup>
import { computed, ref } from 'vue'

import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import FancyBannerSix from '@/Components/Web/FancyBannerSix.vue'
import PricingItem from '@/Components/Web/PricingItem.vue'
import PricingItemTwo from '@/Components/Web/PricingItemTwo.vue'
defineOptions({ layout: DefaultLayout })
const props = defineProps(['plans', 'featured_plans', 'planByDays', 'pricing_page'])
const activeTab = ref(props.planByDays?.[0]?.duration ?? 'monthly')
const filteredPlans = computed(() => {
  return props.plans.filter((p) => {
    if (activeTab.value === 'monthly') return p.days === 30
    if (activeTab.value === 'yearly') return p.days === 365
    return p.days > 365
  })
})
</script>

<template>
  <div class="pricing-section-one bg-one mt-130 xl-mt-130 sm-mt-80 lg-pt-40 lg-pb-80 pt-70 pb-100">
    <div class="container">
      <div class="row">
        <div class="col-xxl-9 col-lg-8 m-auto" data-sal="slide-up" data-sal-duration="900">
          <div class="title-three mb-30 text-center">
            <h2>{{ pricing_page?.title }}</h2>
          </div>
          <!-- /.title-three -->
        </div>
      </div>

      <nav class="pricing-nav-two d-flex justify-content-center mb-10 mt-10">
        <div class="nav justify-content-between">
          <a
            v-for="tab in ['monthly', 'yearly', 'lifetime']"
            :key="tab"
            class="nav-link text-capitalize"
            :class="{ active: activeTab === tab }"
            type="button"
            @click="activeTab = tab"
            >{{ tab }}</a
          >
        </div>
      </nav>

      <div>
        <div class="row gx-xxl-5">
          <div v-for="plan in filteredPlans" class="col-lg-4 d-flex" :key="plan.id">
            <PricingItem :plan />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- featured plans -->
  <div class="pricing-section-three mt-160 lg-mt-80" v-if="featured_plans?.length > 0">
    <div class="container">
      <div class="row">
        <div class="col-lg-5">
          <div class="title-three mb-65 lg-mb-50 pe-xl-5">
            <h2>{{ pricing_page?.featured_plan_title }}</h2>
          </div>
          <!-- /.title-three -->
        </div>

        <div class="col-lg-7">
          <div
            v-for="(featured_plan, index) in featured_plans"
            :key="featured_plan.id"
            class="pr-column"
            :class="{ 'mt-40': index != 0 }"
          >
            <PricingItemTwo :plan="featured_plan" limit="7" />
          </div>
          <!-- /.tab-content -->
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row gx-lg-5 lg-mt-40 mt-60">
      <div class="col-lg-4">
        <div class="card-style-eight mt-40 text-center">
          <div class="icon rounded-circle d-flex align-items-center justify-content-center m-auto">
            <img :src="pricing_page?.feature_one_icon" alt="" />
          </div>
          <h5>{{ pricing_page?.feature_one_title }}</h5>
          <p>
            {{ pricing_page?.feature_one_description }}
          </p>
        </div>
        <!-- /.card-style-eight -->
      </div>
      <div class="col-lg-4">
        <div class="card-style-eight mt-40 text-center">
          <div class="icon rounded-circle d-flex align-items-center justify-content-center m-auto">
            <img :src="pricing_page?.feature_two_icon" alt="" />
          </div>
          <h5>{{ pricing_page?.feature_two_title }}</h5>
          <p>
            {{ pricing_page?.feature_two_description }}
          </p>
        </div>
        <!-- /.card-style-eight -->
      </div>
      <div class="col-lg-4">
        <div class="card-style-eight mt-40 text-center">
          <div class="icon rounded-circle d-flex align-items-center justify-content-center m-auto">
            <img :src="pricing_page?.feature_three_icon" alt="" />
          </div>
          <h5>{{ pricing_page?.feature_three_title }}</h5>
          <p>
            {{ pricing_page?.feature_three_description }}
          </p>
        </div>
        <!-- /.card-style-eight -->
      </div>
    </div>
  </div>

  <FancyBannerSix />
</template>
