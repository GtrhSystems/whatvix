<script setup>
import { Autoplay } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
defineProps(['partner_logos'])
const sliderBreakpoints = {
  240: {
    slidesPerView: 2,
    spaceBetween: 10
  },
  768: {
    slidesPerView: 3,
    spaceBetween: 20
  },
  1024: {
    slidesPerView: 6,
    spaceBetween: 30
  }
}
</script>

<template>
  <div class="partner-logo border-bottom pb-60 pt-60">
    <div class="container">
      <Swiper
        :modules="[Autoplay]"
        :breakpoints="sliderBreakpoints"
        :autoplay="{
          delay: 3000,
          disableOnInteraction: false
        }"
        class="partner-logo-one"
      >
        <SwiperSlide v-for="logo in partner_logos ?? []" class="item" :key="logo">
          <img :src="logo" alt="partner logo" class="m-auto" />
        </SwiperSlide>
      </Swiper>
    </div>
  </div>
</template>
