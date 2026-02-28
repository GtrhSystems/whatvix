<script setup>
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import { Link } from '@inertiajs/vue3'
defineOptions({ layout: DefaultLayout })
const props = defineProps(['faqs', 'categories', 'category'])
</script>

<template>
  <!--
		=====================================================
			FAQ Section One
		=====================================================
		-->
  <div class="faq-section-one mt-200 lg-mt-120">
    <div class="container">
      <div class="border-bottom lg-pb-0 pb-90">
        <div class="row">
          <div class="col-lg-3">
            <div class="sidebar-one md-mb-40">
              <div class="category-list theme-bg-dark-one border-20">
                <ul class="style-none">
                  <li>
                    <Link href="/faq" :class="{ active: category == null }">{{
                      trans('All')
                    }}</Link>
                  </li>
                  <li v-for="cat in categories" :key="cat.id">
                    <Link
                      :href="`/faq?category=${cat.slug}`"
                      :class="{ active: cat.slug == category }"
                      >{{ cat.title }}</Link
                    >
                  </li>
                </ul>
              </div>
              <!-- /.category-list -->
            </div>
            <!-- /.sidebar-one -->
          </div>
          <div class="col-lg-9">
            <div class="ps-lg-5">
              <div class="faq-tag">{{ trans((category ?? 'All') + ' Faq’s') }}</div>

              <div class="accordion accordion-style-one version-two" id="accordionOne">
                <div v-for="(faq, index) in faqs" :key="faq.id" class="accordion-item">
                  <h2 class="accordion-header">
                    <button
                      class="accordion-button"
                      :class="{ collapsed: index != 0 }"
                      type="button"
                      data-bs-toggle="collapse"
                      :data-bs-target="`#collapse${index + 1}`"
                      :aria-expanded="index == 0"
                      :aria-controls="`collapse${index + 1}`"
                    >
                      {{ faq.question }}
                    </button>
                  </h2>
                  <div
                    :id="`collapse${index + 1}`"
                    class="accordion-collapse"
                    :class="{ collapse: index != 0 }"
                    data-bs-parent="#accordionOne"
                  >
                    <div class="accordion-body">
                      <p class="fs-22">
                        {{ faq.answer }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
