<script setup>
import { useForm } from '@inertiajs/vue3'
import moment from 'moment'
import sharedComposable from '@/Composables/sharedComposable'
defineProps(['categories', 'recent_posts', 'tags', 'card'])

const { getQueryParams } = sharedComposable()

const searchForm = useForm({
  s: getQueryParams('s') ?? ''
})

const searchPosts = () => {
  searchForm.get(route('blogs.index'), {
    preserveState: true,
    preserveScroll: true
  })
}
</script>

<template>
  <div class="col-lg-4">
    <div class="sidebar-two me-xxl-5 pe-xxl-4 md-mt-0">
      <div class="sidebar-container bg-one sidebar-search">
        <form @submit.prevent="searchPosts">
          <input v-model="searchForm.s" type="text" placeholder="Search.." />
          <button><i class="bi bi-search"></i></button>
        </form>
      </div>
      <div v-if="categories.length > 0" class="sidebar-container bg-one sidebar-category">
        <h4 class="sidebar-title">{{ trans('Category') }}</h4>
        <ul class="style-none">
          <li v-for="category in categories" :key="category.id">
            <Link :href="route('blogs.category', category.slug)"
              >{{ category.title }} <span>({{ category.post_categories_count }})</span></Link
            >
          </li>
        </ul>
      </div>

      <div v-if="recent_posts.length > 0" class="sidebar-container bg-one blog-recent-news">
        <h4 class="sidebar-title">{{ trans('Recent News') }}</h4>
        <article v-for="blog in recent_posts" class="recent-news" :key="blog.id">
          <div class="post-data">
            <div class="date">{{ moment(blog.created_at).format('DD MMM YYYY') }}</div>
            <Link :href="route('blogs.show', blog.slug)" class="blog-title">
              <h3>{{ blog.title }}</h3>
            </Link>
          </div>
        </article>
      </div>

      <div v-if="tags.length > 0" class="sidebar-container bg-one blog-keyword">
        <h4 class="sidebar-title">{{ trans('Keywords') }}</h4>
        <ul class="style-none d-flex flex-wrap">
          <li v-for="tag in tags" :key="tag.id">
            <Link :href="route('blogs.tag', tag.slug)">{{ tag.title }}</Link>
          </li>
        </ul>
      </div>

      <div v-if="card" class="sidebar-container bg-one contact-banner">
        <h5 v-html="sanitizeHtml(card.title)"></h5>
        <a :href="sanitizeHtml(card.button_link)" class="talk-btn tran3s">{{ card.button_text }}</a>
      </div>
    </div>
    <!-- /.sidebar-two -->
  </div>
</template>
