<script setup>
import FancyBannerOne from '@/Components/Web/FancyBannerOne.vue'
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })
defineProps(['page', 'about_page'])
</script>

<template>
  <div class="faq-section-one mt-160 sm-mt-100 pt-30 md-pt-10">
    <div class="container">
      <div class="border-bottom pb-100 lg-pb-60">
        <div class="title-two mb-50 sm-mb-30">
          <h2 class="text-center">{{ page.title }}</h2>
        </div>
        <div v-html="sanitizeHtml(page.description?.value)"></div>
      </div>
    </div>
  </div>
  <FancyBannerOne :fancy_banner_one="about_page.fancy_banner_one ?? {}" />
</template>
