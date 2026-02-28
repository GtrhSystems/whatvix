<script setup>
defineProps(['faq_section_one', 'faqs'])
</script>

<template>
  <div class="faq-section-one mt-120 lg-mt-70 mb-120 lg-mb-80">
    <div class="container">
      <div class="title-two md-mb-40 mb-60 text-center" data-sal="slide-up" data-sal-duration="900">
        <h2>{{ faq_section_one.title }}</h2>
      </div>
      <!-- /.title-two -->

      <div class="row">
        <div class="col-xxl-10 col-xl-11 m-auto" data-sal="slide-up" data-sal-duration="900">
          <div class="accordion accordion-style-one version-two border-style" id="accordionOne">
            <div v-for="(faq, index) in faqs" :key="index" class="accordion-item">
              <h2 class="accordion-header">
                <button
                  class="accordion-button collapsed"
                  type="button"
                  data-bs-toggle="collapse"
                  :data-bs-target="`#collapse${index + 1}`"
                  aria-expanded="false"
                  :aria-controls="`collapse${index + 1}`"
                >
                  {{ faq.question }}
                </button>
              </h2>
              <div
                :id="`collapse${index + 1}`"
                class="accordion-collapse collapse"
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
</template>
