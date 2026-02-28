<script setup>
defineProps(['block_feature_fourteen'])
</script>
<template>
  <div class="block-feature-fourteen mt-225 md-mt-150">
    <div class="xl container">
      <div class="position-relative z-1">
        <div class="row">
          <div class="col-lg-5 me-auto" data-sal="slide-right" data-sal-duration="900">
            <div class="title-one md-mb-30">
              <h2 class="fw-bold">{{ block_feature_fourteen.title }}</h2>
            </div>
            <!-- /.title-one -->
          </div>
          <div class="col-lg-6" data-sal="slide-left" data-sal-duration="900">
            <div class="accordion accordion-style-one version-two" id="accordionOne">
              <div
                v-for="(feature, index) in block_feature_fourteen.faqs"
                :key="index"
                class="accordion-item"
              >
                <h2 class="accordion-header" :id="'heading' + index">
                  <button
                    class="accordion-button"
                    type="button"
                    :data-bs-toggle="'collapse'"
                    :data-bs-target="'#collapse' + index"
                    :aria-expanded="index === 0 ? 'true' : 'false'"
                    :aria-controls="'collapse' + index"
                  >
                    {{ feature.question }}
                  </button>
                </h2>
                <div
                  :id="'collapse' + index"
                  class="accordion-collapse collapse"
                  :class="{ show: index === 0 }"
                  :aria-labelledby="'heading' + index"
                  data-bs-parent="#accordionOne"
                >
                  <div class="accordion-body">
                    <p class="fs-22">
                      {{ feature.answer }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row gx-xxl-5 pt-70 lg-pt-50 md-pt-10">
          <div class="col-4" data-sal="slide-up" data-sal-duration="900">
            <img
              :src="block_feature_fourteen.image_one ?? '/assets/frontend/images/media/img_43.jpg'"
              alt=""
              class="img-meta w-100 mt-25"
            />
          </div>
          <div class="col-8" data-sal="slide-up" data-sal-duration="900" data-sal-delay="100">
            <img
              :src="block_feature_fourteen.image_two ?? '/assets/frontend/images/media/img_44.jpg'"
              alt=""
              class="img-meta w-100 mt-25"
            />
          </div>
        </div>

        <img
          :src="
            block_feature_fourteen.image_three ?? '/assets/frontend/images/graphics/screen_33.png'
          "
          alt=""
          class="shapes shape_01"
        />
      </div>
    </div>
  </div>
  <!-- /.block-feature-fourteen -->
</template>
