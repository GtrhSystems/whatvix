<script setup>
import BlockFeatureTwentyFive from '@/Components/Web/BlockFeatureTwentyFive.vue'
import CounterSectionOne from '@/Components/Web/CounterSectionOne.vue'
import FancyBannerSix from '@/Components/Web/FancyBannerSix.vue'
import Pagination from '@/Components/Web/Pagination.vue'
import sharedComposable from '@/Composables/sharedComposable'
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
defineOptions({ layout: DefaultLayout })
const props = defineProps(['services', 'categories', 'service_page'])
const { textExcerpt } = sharedComposable()
</script>

<template>
  <div class="block-feature-nineteen pt-90 lg-pt-80 mt-110 xl-mt-90">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 m-auto text-center" data-sal="slide-up" data-sal-duration="800">
          <div class="title-three mb-20">
            <h2>{{ service_page?.list?.title }}</h2>
          </div>
          <!-- /.title-three -->
          <p class="fs-24 pe-xxl-4 ps-xxl-4">
            {{ service_page?.list?.subtitle }}
          </p>
        </div>
      </div>

      <div class="row gx-xxl-5">
        <div
          v-for="(service, index) in services.data"
          :key="service.id"
          class="col-lg-6"
          data-sal="slide-up"
          data-sal-duration="900"
          :data-sal-delay="100 + index * 100"
        >
          <div class="card-style-six bg-two position-relative tran3s mt-50 lg-mt-30">
            <div
              class="icon tran3s rounded-circle d-flex align-items-center justify-content-center"
            >
              <img :src="sanitizeHtml(service.icon)" alt="icon" />
            </div>
            <h4>{{ service.title }}</h4>
            <p>{{ textExcerpt(service.overview, 100) }}</p>
            <Link
              :href="route('services.show', service.slug)"
              class="stretched-link d-block"
            ></Link>
          </div>
        </div>
      </div>

      <Pagination :links="services.links" />
    </div>
  </div>

  <BlockFeatureTwentyFive
    :block_feature_twenty_five="service_page.block_feature_twenty_five ?? {}"
  />

  <CounterSectionOne :counter_section_one="service_page.counter_section_one ?? {}" />
  <FancyBannerSix :fancy_banner_six="service_page.fancy_banner_six ?? {}" />
</template>
