<script setup>
import sal from 'sal.js'
import Header from '@/Layouts/Default/Header.vue'
import Footer from '@/Layouts/Default/Footer.vue'

import SeoMeta from '@/Components/Web/SeoMeta.vue'
import ScrollToTopBtn from '@/Layouts/Default/ScrollToTopBtn.vue'
import ToastrContainer from '@/Components/ToastrContainer.vue'
import { onMounted, onUpdated } from 'vue'
onMounted(() => sal())
onUpdated(() => sal())
</script>

<template>
  <SeoMeta />
  <ToastrContainer />
  <div class="main-page-wrapper dark-bg-three">
    <Header />
    <slot />
    <ScrollToTopBtn />
    <Footer />
  </div>
</template>
