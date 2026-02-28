<script setup>
import moment from 'moment'

defineProps(['blog_section_one', 'featured_blogs'])
</script>
<template>
  <div class="blog-section-one mt-120 lg-mt-80">
    <div class="container">
      <div class="position-relative">
        <div class="row">
          <div class="col-lg-8" data-sal="slide-up" data-sal-duration="900">
            <div class="title-one">
              <h2>{{ blog_section_one.title }}</h2>
            </div>
            <!-- /.title-one -->
          </div>
        </div>

        <div class="row gx-xl-5" v-if="featured_blogs?.length">
          <div class="col-lg-7 col-md-6" data-sal="slide-up" data-sal-duration="900">
            <article class="blog-meta-one mt-35">
              <div class="media position-relative">
                <img
                  :src="sanitizeHtml(featured_blogs[0].preview?.value)"
                  alt="preview"
                  class="w-100"
                />
                <div class="date">{{ moment(featured_blogs[0].created_at).format('DD MMM') }}</div>
              </div>
              <!-- /.media -->
              <div class="row">
                <div class="col-xl-9">
                  <a :href="sanitizeHtml(featured_blogs[0].url)" class="blog-title"
                    ><h3>{{ featured_blogs[0].title }}</h3></a
                  >
                </div>
              </div>
            </article>
          </div>
          <div
            v-if="featured_blogs?.length > 1"
            class="col-lg-5 col-md-6"
            data-sal="slide-up"
            data-sal-duration="900"
            data-sal-delay="200"
          >
            <article class="blog-meta-one mt-35">
              <div class="media position-relative">
                <img
                  :src="sanitizeHtml(featured_blogs[1].preview?.value)"
                  alt="preview"
                  class="w-100"
                />
                <div class="date">{{ moment(featured_blogs[1].created_at).format('DD MMM') }}</div>
              </div>
              <!-- /.media -->
              <div class="row">
                <div class="col-xl-9">
                  <a :href="sanitizeHtml(featured_blogs[1].url)" class="blog-title"
                    ><h3>{{ featured_blogs[1].title }}</h3></a
                  >
                </div>
              </div>
            </article>
            <!-- /.blog-meta-one -->
          </div>
        </div>

        <div class="section-btn md-mt-50 text-center" data-sal="slide-up" data-sal-duration="900">
          <Link href="/blogs" class="btn-two">{{ blog_section_one.button_text }}</Link>
        </div>
      </div>
    </div>
  </div>
</template>
