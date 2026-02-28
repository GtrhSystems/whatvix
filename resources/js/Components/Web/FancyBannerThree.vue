<script setup>
defineProps(['fancy_banner_three'])

const textWithSpan = (text) => {
  return text?.replace(/{([^}]+)}/g, '<span>$1</span>')
}
</script>
<template>
  <div class="fancy-banner-three position-relative z-1 pt-90 lg-pt-70 pb-100 lg-pb-70">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-xxl-7 col-lg-6" data-sal="slide-right" data-sal-duration="900">
          <h2 v-html="sanitizeHtml(textWithSpan(fancy_banner_three.title))"></h2>
        </div>
        <div class="col-xxl-5 col-lg-6" data-sal="slide-left" data-sal-duration="900">
          <p class="fs-24 lg-mb-20 mb-40">
            {{ fancy_banner_three.subtitle }}
          </p>
          <a :href="sanitizeUrl(fancy_banner_three.button_link)" class="btn-seven">
            {{ fancy_banner_three.button_text }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
