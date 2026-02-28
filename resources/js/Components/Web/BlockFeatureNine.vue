<script setup>
defineProps(['block_feature_nine'])
</script>
<template>
  <div class="block-feature-nine mt-120 lg-mt-100">
    <div class="container">
      <div class="feature-block">
        <div class="row gx-xxl-5 align-items-center">
          <div class="col-lg-6 order-lg-last">
            <div data-sal="slide-left" data-sal-duration="900">
              <div class="title-two mb-30 md-mb-20">
                <div class="upper-title">{{ block_feature_nine.card_one_top_title }}</div>
                <h2>{{ block_feature_nine.card_one_title }}</h2>
              </div>
              <!-- /.title-one -->
              <p class="fs-24 m0">
                {{ block_feature_nine.card_one_subtitle }}
              </p>
            </div>
          </div>
          <div class="col-lg-6 col-md-8 m-auto">
            <div
              class="img-box d-flex align-items-center justify-content-center me-lg-5 md-mt-40"
              data-sal="slide-right"
              data-sal-duration="900"
            >
              <img
                :src="
                  sanitizeHtml(block_feature_nine.card_one_left_image) ??
                  '/assets/frontend/images/graphics/screen_20.png'
                "
                alt=""
                class="w-100"
              />
            </div>
            <!-- /.img-box -->
          </div>
        </div>
      </div>
      <!-- /.feature-block -->

      <div class="feature-block mt-100 lg-mt-80">
        <div class="row gx-xxl-5 align-items-center">
          <div class="col-lg-6">
            <div class="me-xl-5" data-sal="slide-right" data-sal-duration="900">
              <div class="title-two mb-30 md-mb-20">
                <div class="upper-title">{{ block_feature_nine.card_two_top_title }}</div>
                <h2>{{ block_feature_nine.card_two_title }}</h2>
              </div>
              <!-- /.title-one -->
              <p class="fs-24 m0">
                {{ block_feature_nine.card_two_subtitle }}
              </p>
            </div>
          </div>
          <div class="col-lg-6 col-md-8 m-auto">
            <div
              class="img-box d-flex align-items-center justify-content-center me-xxl-5 md-mt-40 pt-40"
              data-sal="slide-left"
              data-sal-duration="900"
            >
              <img
                :src="
                  sanitizeHtml(block_feature_nine.card_two_left_image) ??
                  '/assets/frontend/images/graphics/screen_21.png'
                "
                alt=""
                class="w-100"
              />
            </div>
            <!-- /.img-box -->
          </div>
        </div>
      </div>
      <!-- /.feature-block -->

      <div class="feature-block mt-100 lg-mt-80">
        <div class="row gx-xxl-5 align-items-center">
          <div class="col-lg-6 order-lg-last">
            <div data-sal="slide-left" data-sal-duration="900">
              <div class="title-two mb-30 md-mb-20">
                <div class="upper-title">{{ block_feature_nine.card_three_top_title }}</div>
                <h2>{{ block_feature_nine.card_three_title }}</h2>
              </div>
              <!-- /.title-one -->
              <p class="fs-24 m0">
                {{ block_feature_nine.card_three_subtitle }}
              </p>
            </div>
          </div>
          <div class="col-lg-6 col-md-8 m-auto">
            <div
              class="img-box d-flex align-items-center justify-content-center me-lg-5 md-mt-40 pt-20"
              data-sal="slide-right"
              data-sal-duration="900"
            >
              <img
                :src="
                  sanitizeHtml(block_feature_nine.card_three_left_image) ??
                  '/assets/frontend/images/graphics/screen_22.png'
                "
                alt=""
                class="w-100"
              />
            </div>
            <!-- /.img-box -->
          </div>
        </div>
      </div>
      <!-- /.feature-block -->
    </div>
  </div>
</template>
