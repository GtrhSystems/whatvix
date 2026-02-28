<script setup>
import { ref } from 'vue'
import FancyBannerSix from '@/Components/Web/FancyBannerSix.vue'
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
defineOptions({ layout: DefaultLayout })
const props = defineProps(['service', 'service_page', 'categories'])
const activeAccordion = ref(0)
</script>

<template>
  <div class="service-details pt-85 md-pt-60 mt-110">
    <div class="container">
      <div class="row">
        <div class="col-xxl-9 col-lg-8">
          <div class="text-meta pe-xxl-4">
            <div class="title-three mb-20">
              <h2>{{ service.title }}</h2>
            </div>
            <p class="fw-bold">{{ service.overview }}</p>
            <div class="mb-10">
              <img :src="sanitizeHtml(service.preview)" />
            </div>
            <div class="mt-30" v-html="sanitizeHtml(service.description)"></div>

            <div class="accordion accordion-style-three mt-5">
              <div v-for="(faq, index) in service.faqs ?? []" :key="faq.id" class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button" type="button" @click="activeAccordion = index">
                    {{ faq.question }}
                  </button>
                </h2>
                <div
                  class="accordion-collapse collapse"
                  :class="{ show: index === activeAccordion }"
                >
                  <div class="accordion-body">
                    <p>{{ faq.answer }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xxl-3 col-lg-4">
          <div class="sidebar-one md-mt-60">
            <div class="category-list theme-bg-dark-one border-20">
              <ul class="style-none">
                <li v-for="category in categories" :key="category.id">
                  <Link
                    :href="route('service-category', category.slug)"
                    :class="{ active: category.id == service.category_id }"
                  >
                    {{ category.title }}
                  </Link>
                </li>
              </ul>
            </div>
            <!-- /.category-list -->

            <div v-if="service_page.card" class="contact-banner border-20 mt-30 text-center">
              <h5>{{ service_page.card.title }}</h5>
              <a :href="sanitizeUrl(service_page.card.button_link)" class="talk-btn tran3s">
                {{ service_page.card.button_text }}</a
              >
            </div>
            <!-- /.contact-banner -->
          </div>
          <!-- /.sidebar-one -->
        </div>
      </div>
    </div>
  </div>

  <FancyBannerSix />
</template>
