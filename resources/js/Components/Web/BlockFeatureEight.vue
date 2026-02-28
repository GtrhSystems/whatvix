<script setup>
const props = defineProps(['block_feature_eight'])
</script>
<template>
  <div class="block-feature-eight position-relative z-1 pt-100 lg-pt-80">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 m-auto" data-sal="slide-up" data-sal-duration="900">
          <div class="title-two lg-mb-40 mb-60 text-center">
            <h2>{{ block_feature_eight.title }}</h2>
            <p class="fw-500 fs-28 mt-25">
              {{ block_feature_eight.subtitle }}
            </p>
          </div>
          <!-- /.title-one -->
        </div>
      </div>

      <div
        class="d-flex justify-content-center"
        data-sal="slide-up"
        data-sal-duration="900"
        data-sal-delay="200"
      >
        <div class="image-screen position-relative z-1">
          <img
            :src="
              sanitizeHtml(block_feature_eight.bg_image) ??
              '/assets/frontend/images/graphics/screen_17.png'
            "
            alt=""
          />
          <img
            :src="
              sanitizeHtml(block_feature_eight.shape_left) ??
              '/assets/frontend/images/shape/shape_11.svg'
            "
            alt=""
            class="shapes shape_01"
          />
          <img
            :src="
              sanitizeHtml(block_feature_eight.shape_right) ??
              '/assets/frontend/images/shape/shape_12.svg'
            "
            alt=""
            class="shapes shape_02"
          />
          <img
            :src="
              sanitizeHtml(block_feature_eight.card_top) ??
              '/assets/frontend/images/graphics/screen_18.png'
            "
            alt=""
            class="shapes card_01"
          />
          <img
            :src="
              sanitizeHtml(block_feature_eight.card_bottom) ??
              '/assets/frontend/images/graphics/screen_19.png'
            "
            alt=""
            class="shapes card_02"
          />
        </div>
      </div>
    </div>
  </div>
</template>
