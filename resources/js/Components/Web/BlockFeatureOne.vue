<script setup>
defineProps(['block_feature_one'])
</script>

<template>
  <div
    class="block-feature-one bg-one position-relative z-1 lg-mt-80 pt-140 lg-pt-80 pb-150 lg-pb-70 mt-80"
  >
    <div class="xl container">
      <div class="row gx-xl-5">
        <div class="col-lg-5">
          <div class="title-one md-mb-40" data-sal="slide-right" data-sal-duration="900">
            <h2>{{ block_feature_one.title }}</h2>
          </div>
          <!-- /.title-one -->
        </div>

        <div class="col-lg-7">
          <div class="ps-xxl-5 ms-xxl-4">
            <div class="row gx-0">
              <div
                v-for="feature in block_feature_one?.features ?? []"
                :key="feature"
                class="col-12"
              >
                <div
                  class="card-style-one style-three d-flex"
                  data-sal="slide-up"
                  data-sal-duration="900"
                >
                  <div class="icon rounded-circle d-flex align-items-center justify-content-center">
                    <img
                      :src="
                        sanitizeHtml(feature.icon ?? '/assets/frontend/images/icon/icon_05.svg')
                      "
                      alt=""
                    />
                  </div>
                  <div class="text">
                    <h4 class="font-poppins fw-600">{{ feature.title }}</h4>
                    <p class="fs-22">
                      {{ feature.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <img
      :src="
        sanitizeHtml(block_feature_one.image_one ?? '/assets/frontend/images/shape/shape_02.png')
      "
      alt=""
      class="shapes shape_01"
      data-sal="slide-right"
      data-sal-duration="500"
    />
  </div>
</template>
