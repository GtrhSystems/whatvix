<script setup>
defineProps(['fancy_banner_one'])
</script>
<template>
  <div class="fancy-banner-one mt-120 lg-mt-80">
    <div class="container">
      <div class="bg-wrapper position-relative z-1 pt-85 lg-pt-60 pb-95 lg-pb-70 text-center">
        <div class="row">
          <div class="col-lg-8 m-auto" data-sal="slide-up" data-sal-duration="900">
            <h2>{{ fancy_banner_one.title }}</h2>
          </div>
        </div>
        <div data-sal="slide-up" data-sal-duration="900" data-sal-delay="200">
          <a :href="sanitizeUrl(fancy_banner_one.button_link)" class="btn-two invert">{{
            fancy_banner_one.button_text
          }}</a>
        </div>
        <img
          :src="
            sanitizeHtml(fancy_banner_one.shape_one ?? '/assets/frontend/images/shape/shape_05.png')
          "
          alt=""
          class="shapes shape_01"
        />
        <img
          :src="
            sanitizeHtml(fancy_banner_one.shape_two ?? '/assets/frontend/images/shape/shape_06.png')
          "
          alt=""
          class="shapes shape_02"
        />
      </div>
      <!-- /.bg-wrapper -->
    </div>
  </div>
</template>
