<script setup>
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import Sidebar from './Partials/Sidebar.vue'
import { Link } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
import moment from 'moment'

const { socialShare } = sharedComposable()

defineOptions({ layout: DefaultLayout })
defineProps(['blog', 'categories', 'recent_posts', 'tags', 'blog_page'])
</script>
<template>
  <div class="blog-details theme-bg-dark-two mt-130 sm-mt-80 pt-70 lg-pt-60 pb-30 lg-pb-20">
    <div class="container">
      <div class="position-relative">
        <div class="row gx-xxl-5">
          <div class="col-lg-8">
            <div class="ps-xxl-5 ms-xxl-4">
              <article class="blog-meta-three mb-50">
                <div class="media position-relative">
                  <img :src="sanitizeHtml(blog.preview?.value)" alt="" class="w-100" />
                  <div class="date">{{ moment(blog.created_at).format('DD MMM') }}</div>
                </div>
                <!-- /.media -->
                <div class="post bg-one">
                  <div class="blog-title">
                    <h3>{{ blog.title }}</h3>
                  </div>

                  <div class="post-details-meta">
                    {{ blog.short_description?.value }}
                    <hr />
                    <div v-html="sanitizeHtml(blog.long_description?.value)"></div>
                  </div>
                  <!-- /.post-details-meta -->

                  <div class="bottom-widget d-sm-flex align-items-center justify-content-between">
                    <ul class="d-flex align-items-center tags style-none pt-20">
                      <li v-for="item in blog.categories" :key="item.id">
                        <Link
                          v-if="item.type == 'blog_category'"
                          :href="route('blogs.category', item.slug)"
                          >{{ item.title }}</Link
                        >
                        <Link v-else :href="route('blogs.tag', item.slug)">{{ item.title }}</Link>
                      </li>
                    </ul>
                    <ul class="d-flex share-icon align-items-center style-none pt-20">
                      <li>{{ trans('Share') }}:</li>
                      <li v-for="platform in ['facebook', 'twitter', 'instagram']" :key="platform">
                        <a
                          :href="socialShare(platform, `${route('blogs.show', blog.slug)}`)"
                          target="_blank"
                          ><i class="bi" :class="`bi-${platform}`"></i
                        ></a>
                      </li>
                    </ul>
                  </div>
                </div>
              </article>
              <!-- /.blog-meta-three -->
            </div>
          </div>

          <Sidebar
            :categories="categories"
            :recent_posts="recent_posts"
            :tags="tags"
            :card="blog_page.card ?? {}"
          />
        </div>
      </div>
    </div>
  </div>
</template>
