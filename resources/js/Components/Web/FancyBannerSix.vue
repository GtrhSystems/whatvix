<script setup>
import getOption from '@/Composables/optionComposable'
import { onMounted, ref } from 'vue'

const fancy_banner_six = ref({})

onMounted(async () => {
  fancy_banner_six.value = await getOption('primary_data', 'fancy_banner_six')
})

const titleWithImg = (title, image) => {
  if (title && image) return title.replace(/\{.*?\}/g, `<img src="${image}">`)
  return title
}
</script>
<template>
  <div class="fancy-banner-six mt-100 lg-mt-80">
    <div class="container">
      <div
        class="bg-wrapper bg-one position-relative z-1 pt-85 lg-pt-60 pb-60 pe-2 ps-2 text-center"
      >
        <div class="row">
          <div class="col-md-9 m-auto" data-sal="slide-up" data-sal-duration="900">
            <h2
              class="mb-35"
              v-html="
                sanitizeHtml(titleWithImg(fancy_banner_six?.title, fancy_banner_six?.title_image))
              "
            ></h2>
          </div>
        </div>
        <p class="fs-28" data-sal="slide-up" data-sal-duration="900" data-sal-delay="100">
          {{ fancy_banner_six?.subtitle }}
        </p>
        <div data-sal="slide-up" data-sal-duration="900" data-sal-delay="200">
          <a :href="sanitizeUrl(fancy_banner_six?.button_link)" class="btn-fourteen mt-20">{{
            fancy_banner_six?.button_text
          }}</a>
        </div>

        <img
          :src="
            sanitizeHtml(fancy_banner_six.shape_one ?? '/assets/frontend/images/icon/icon_52.png')
          "
          alt=""
          class="shapes shape_01"
        />
        <img
          :src="
            sanitizeHtml(fancy_banner_six.shape_two ?? '/assets/frontend/images/icon/icon_53.png')
          "
          alt=""
          class="shapes shape_02"
        />
      </div>
      <!-- /.bg-wrapper -->
    </div>
  </div>
</template>
