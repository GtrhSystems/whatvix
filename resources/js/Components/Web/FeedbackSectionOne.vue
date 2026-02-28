<script setup>
import { Autoplay } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import sharedComposable from '@/Composables/sharedComposable'
import { ref } from 'vue'
defineProps(['testimonials', 'feedback_section_one', 'hidePicture'])
const { textExcerpt } = sharedComposable()
const sliderBreakpoints = {
  640: {
    slidesPerView: 1
  },
  768: {
    slidesPerView: 2
  }
}
const activeIndex = ref(0)
const swiperRef = ref(null)

function onSlideChange(swiper) {
  activeIndex.value = swiper.realIndex
}

function setSwiperInstance(swiper) {
  swiperRef.value = swiper
}

function goToSlide(index) {
  if (swiperRef.value) {
    swiperRef.value.slideTo(index)
  }
}
</script>

<template>
  <div
    class="feedback-section-one bg-one position-relative z-1 pt-130 lg-pt-80 pb-150 lg-pb-70 mt-130 lg-mt-80"
  >
    <div class="container">
      <div class="title-one mb-70 md-mb-40 text-center" data-sal="slide-up" data-sal-duration="900">
        <h2>{{ feedback_section_one.title }}</h2>
      </div>
      <!-- /.title-one -->
      <Swiper
        :modules="[Autoplay]"
        :breakpoints="sliderBreakpoints"
        @slideChange="onSlideChange"
        @swiper="setSwiperInstance"
        :loop="true"
        class="feedback-slide-one"
      >
        <SwiperSlide v-for="item in testimonials ?? []" class="item" :key="item.id">
          <div class="feedback-card-one">
            <div class="icon rounded-circle d-flex align-items-center justify-content-center">
              <img
                :src="hidePicture ? '/assets/frontend/images/icon/icon_12.svg' : item.image"
                alt=""
              />
            </div>
            <blockquote>“{{ textExcerpt(item.description, 80) }}”</blockquote>
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6>{{ item.name }}</h6>
                <span>{{ item.designation }}</span>
              </div>
              <ul class="style-none d-flex rating">
                <li v-for="i in item.ratting" :key="i"><i class="bi bi-star-fill"></i></li>
                <li v-for="i in 5 - item.ratting" :key="i"><i class="bi bi-star"></i></li>
              </ul>
            </div>
          </div>
        </SwiperSlide>
      </Swiper>
    </div>
    <div class="d-flex justify-content-center mt-30 gap-2" v-if="testimonials.length > 1">
      <button
        v-for="(slide, index) in testimonials.length"
        :key="'bullet-' + index"
        @click="goToSlide(index)"
        type="button"
        class="rounded-circle feedback-section-one-slider p-0"
        :class="
          activeIndex === index
            ? 'feedback-section-one-slider-active'
            : 'feedback-section-one-slider-inactive'
        "
      ></button>
    </div>
    <!-- /.container -->
    <img
      :src="
        sanitizeHtml(feedback_section_one.shape ?? '/assets/frontend/images/shape/shape_04.png')
      "
      alt=""
      class="shapes shape_01"
      data-sal="slide-left"
      data-sal-duration="500"
    />
  </div>
</template>
