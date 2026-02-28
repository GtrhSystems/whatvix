<script setup>
defineProps(['block_feature_seven'])
</script>
<template>
  <div class="block-feature-seven mt-50 sm-mt-10 sm-mb-10 mb-50">
    <div class="container">
      <div class="row justify-content-between">
        <div
          v-for="item in block_feature_seven.feature_list"
          :key="item.id"
          class="col-lg-4 col-md-6"
          data-sal="slide-up"
          data-sal-duration="900"
        >
          <div class="d-flex align-items-center mb-20 mt-20 block">
            <div
              class="icon rounded-circle d-flex align-items-center justify-content-center"
              :style="{ background: item.color ?? '#01f1e1' }"
            >
              <img src="/assets/frontend/images/icon/icon_29.svg" alt="" />
            </div>
            <h4 class="font-outfit">{{ item.title }}</h4>
          </div>
          <!-- /.block -->
        </div>
      </div>
    </div>
  </div>
</template>
