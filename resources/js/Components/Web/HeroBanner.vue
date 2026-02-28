<script setup>
import { ref } from 'vue'
defineProps(['hero_banner'])
import { useForm } from '@inertiajs/vue3'

const email = ref('')

const goRegister = () => {
  window.location.href = '/register?email=' + email.value
}
</script>

<template>
  <div
    class="hero-banner-three position-relative z-1 pt-250 lg-pt-200 sm-pt-150 pb-130 lg-pb-70 sm-pb-10 overflow-hidden"
  >
    <div class="position-relative container">
      <div class="row">
        <div class="col-xl-6 col-lg-7 col-md-8">
          <h1 class="hero-heading" data-sal="slide-up" data-sal-duration="900">
            {{ hero_banner.title }}
          </h1>
          <p
            class="fs-28 fw-500 mt-35 mb-55"
            data-sal="slide-up"
            data-sal-duration="900"
            data-sal-delay="300"
          >
            {{ hero_banner.subtitle }}
          </p>
          <div data-sal="slide-up" data-sal-duration="900" data-sal-delay="400">
            <form
              @submit.prevent="goRegister"
              class="d-flex align-items-center flex-column justify-content-between flex-sm-row hero-banner-email mb-5 flex-wrap"
            >
              <input
                type="email"
                v-model="email"
                class="hero-banner-email-input"
                placeholder="Email address.."
                required
              />
              <button type="submit" class="hero-banner-email-btn">
                {{ hero_banner.btn_text }}
              </button>
            </form>
            <ul class="style-none d-flex flex-wrap">
              <li><i class="bi bi-check2"></i> {{ hero_banner.feature_item_one }}</li>
              <li><i class="bi bi-check2"></i> {{ hero_banner.feature_item_two }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- /.container -->
    <div class="illustration-holder">
      <img
        :src="sanitizeHtml(hero_banner.bg_image ?? '/assets/frontend/images/graphics/man_01.png')"
        alt=""
        class="shapes man-img"
      />
      <img
        :src="
          sanitizeHtml(
            hero_banner.bg_img_overflow ?? '/assets/frontend/images/graphics/screen_16.png'
          )
        "
        alt=""
        class="shapes shape_01"
        data-sal="slide-left"
        data-sal-duration="900"
        data-sal-delay="400"
      />
    </div>
    <!-- /.illustration-holder -->
  </div>
</template>
