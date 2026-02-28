<script setup>
import { Autoplay } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
defineProps(['app_integration_three'])
const sliderBreakpoints = {
  400: {
    slidesPerView: 4
  },
  700: {
    slidesPerView: 5
  },
  1200: {
    slidesPerView: 6
  },
  1400: {
    slidesPerView: 7
  }
}
</script>

<template>
  <div class="app-integration-three position-relative z-1 pt-140 lg-pt-100">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 m-auto">
          <div class="title-one mb-70 lg-mb-40 text-center">
            <h2>{{ app_integration_three.title }}</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="logo-wrapper">
      <Swiper
        :breakpoints="sliderBreakpoints"
        :modules="[Autoplay]"
        space-between="10"
        :autoplay="{
          delay: 3000,
          disableOnInteraction: false
        }"
        class="partner-logo-two"
      >
        <SwiperSlide v-for="(item, i) in app_integration_three.logos" :key="i" class="item">
          <div
            class="logo-box d-flex align-items-center justify-content-center"
            :style="{ background: item.color }"
          >
            <img :src="item.icon" alt="" />
          </div>
        </SwiperSlide>
      </Swiper>
      <!-- /.partner-logo-two -->
    </div>

    <div class="mt-70 lg-mt-40 text-center">
      <p class="fs-28 fw-500 mb-25">{{ app_integration_three.subtitle }}</p>
      <a :href="sanitizeUrl(app_integration_three.button_link)" class="btn-eleven">{{
        app_integration_three.button_text
      }}</a>
    </div>
  </div>
</template>
