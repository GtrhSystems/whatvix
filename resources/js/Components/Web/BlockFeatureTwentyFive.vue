<script setup>
defineProps(['block_feature_twenty_five'])
</script>

<template>
  <div class="block-feature-twentyFive mt-180 lg-mt-100">
    <div class="container">
      <div class="row">
        <div
          class="col-lg-5 order-lg-last sal-animate"
          data-sal="slide-left"
          data-sal-duration="900"
        >
          <div class="pt-45 md-pt-10 pb-35 md-pb-10">
            <div class="title-three mb-35">
              <div class="upper-title color-two text-capitalize">
                {{ block_feature_twenty_five.top_title }}
              </div>
              <h2>{{ block_feature_twenty_five.title }}</h2>
            </div>
            <!-- /.title-three -->

            <p class="fs-24 mb-50">
              {{ block_feature_twenty_five.subtitle }}
            </p>

            <ul class="features style-none">
              <li
                v-for="(item, index) in block_feature_twenty_five.features ?? []"
                :key="item.id"
                class="d-flex align-items-center"
                :class="{ 'mt-50': index !== 0 }"
              >
                <div class="icon rounded-circle d-flex align-items-center justify-content-center">
                  <img :src="item.icon" alt="" />
                </div>
                <div class="text">{{ item.title }}</div>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-lg-7 sal-animate" data-sal="slide-right" data-sal-duration="900">
          <div
            class="media-wrapper version-two position-relative"
            :style="{ 'background-image': `url(${block_feature_twenty_five.image})` }"
          >
            <div class="counter-box d-flex flex-column align-items-center justify-content-center">
              <div class="main-count fw-500">
                {{ block_feature_twenty_five.counter.prefix
                }}<span class="counter">{{ block_feature_twenty_five.counter.number }}</span
                >{{ block_feature_twenty_five.counter.suffix }}
              </div>
              <p>{{ block_feature_twenty_five.counter.subtitle }}</p>
            </div>
          </div>
          <!-- /.media-wrapper -->
        </div>
      </div>
    </div>
  </div>
</template>
