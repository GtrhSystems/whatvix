<script setup>
import { Autoplay } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import sharedComposable from '@/Composables/sharedComposable'
import { ref } from 'vue'

defineProps(['testimonials', 'feedback_section_four'])
const { textExcerpt } = sharedComposable()

// Configure slider breakpoints for responsive design
const sliderBreakpoints = {
  640: {
    slidesPerView: 1,
    spaceBetween: 10
  },
  1024: {
    slidesPerView: 2,
    spaceBetween: 20
  },
  1280: {
    slidesPerView: 3,
    spaceBetween: 30
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

function goToPrevSlide() {
  if (swiperRef.value) {
    swiperRef.value.slidePrev()
  }
}

function goToNextSlide() {
  if (swiperRef.value) {
    swiperRef.value.slideNext()
  }
}
</script>

<template>
  <div class="feedback-section-five version-two mt-100 lg-mt-80">
    <div class="container">
      <div class="position-relative z-1">
        <div class="row">
          <div class="col-lg-5">
            <div class="title-three">
              <div class="upper-title mb-10">{{ feedback_section_four?.title }}</div>
              <h2 class="fw-500">{{ feedback_section_four?.subtitle }}</h2>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="slider-wrapper">
              <Swiper
                :modules="[Autoplay]"
                :loop="true"
                :space-between="10"
                :breakpoints="sliderBreakpoints"
                @slideChange="onSlideChange"
                @swiper="setSwiperInstance"
                class="feedback-slider-three"
              >
                <SwiperSlide v-for="item in testimonials ?? []" class="item" :key="item.id">
                  <div class="feedback-card-three">
                    <img src="/assets/frontend/images/icon/icon_33.svg" alt="" />
                    <blockquote>"{{ textExcerpt(item.description, 80) }}"</blockquote>
                    <div class="d-flex align-items-center">
                      <img
                        :src="
                          sanitizeHtml(item.image ?? '/assets/frontend/images/graphics/screen_23.png')
                        "
                        alt="feedback-avatar"
                        class="c-img rounded-circle"
                      />
                      <div class="ps-3">
                        <h6>{{ item.name }}</h6>
                        <span>{{ item.designation || item.location || '' }}</span>
                      </div>
                    </div>
                  </div>
                </SwiperSlide>
              </Swiper>
            </div>
          </div>
        </div>
        <ul class="slider-arrows d-flex justify-content-center style-none">
          <li @click="goToPrevSlide" class="prev_b slick-arrow">
            <img src="/assets/frontend/images/icon/icon_34.svg" alt="" class="icon icon-dark" />
            <img src="/assets/frontend/images/icon/icon_34.svg" alt="" class="icon icon-white" />
          </li>
          <li @click="goToNextSlide" class="next_b slick-arrow">
            <img src="/assets/frontend/images/icon/icon_35.svg" alt="" class="icon icon-dark" />
            <img src="/assets/frontend/images/icon/icon_35.svg" alt="" class="icon icon-white" />
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
