<script setup>
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import Sidebar from './Partials/Sidebar.vue'
import moment from 'moment'
import { Link } from '@inertiajs/vue3'
import Pagination from '@/Components/Web/Pagination.vue'
defineOptions({ layout: DefaultLayout })
defineProps(['posts', 'categories', 'recent_posts', 'tags', 'blog_page'])
</script>
<template>
  <div class="blog-section-four theme-bg-dark-two mt-130 sm-mt-80 pt-70 lg-pt-60 lg-pb-40 pb-30">
    <div class="container">
      <div class="position-relative">
        <div class="row gx-xxl-5">
          <div class="col-lg-8">
            <div class="ps-xxl-5 ms-xxl-4" v-if="posts.total">
              <article
                v-for="blog in posts.data"
                :key="blog.id"
                class="blog-meta-three mb-50 lg-mb-40"
              >
                <div class="media position-relative">
                  <img :src="sanitizeHtml(blog.preview?.value)" alt="" class="w-100" />
                  <div class="date">{{ moment(blog.created_at).format('DD MMM') }}</div>
                </div>
                <!-- /.media -->
                <div class="post bg-one">
                  <ul class="author style-none d-flex align-items-center">
                    <template v-for="(category, index) in blog.categories" :key="category.id">
                      <li>
                        <Link :href="route('blogs.category', category.slug)">
                          {{ category.title }}
                        </Link>
                      </li>
                      <li v-if="index < blog.categories.length - 1">.</li>
                    </template>
                  </ul>
                  <Link :href="route('blogs.show', blog.slug)" class="blog-title"
                    ><h3>{{ blog.title }}</h3>
                  </Link>
                </div>
              </article>
              <!-- /.blog-meta-three -->

              <Pagination :links="posts.links" />
            </div>
            <div v-else>
              <h3 class="text-center">{{ trans('No blog found') }}</h3>
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
