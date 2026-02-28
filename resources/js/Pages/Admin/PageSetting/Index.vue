<script setup>
import { ref, shallowRef, computed } from 'vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import Primary from '@/Pages/Admin/PageSetting/Primary.vue'
import AuthPages from '@/Pages/Admin/PageSetting/AuthPages.vue'
import Home from '@/Pages/Admin/PageSetting/Home.vue'
import About from '@/Pages/Admin/PageSetting/About.vue'
import Contact from '@/Pages/Admin/PageSetting/Contact.vue'
import Service from '@/Pages/Admin/PageSetting/Service.vue'
import Team from '@/Pages/Admin/PageSetting/Team.vue'
import Blog from '@/Pages/Admin/PageSetting/Blog.vue'
import Pricing from '@/Pages/Admin/PageSetting/Pricing.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps([
  'primary_data',
  'auth_pages',
  'home_page',
  'service_page',
  'about_page',
  'contact_page',
  'team_page',
  'blog_page',
  'pricing_page'
])
const tabs = [
  { id: 'primary', label: 'Primary', dataKey: 'primary_data', component: Primary },
  { id: 'home', label: 'Home Page', dataKey: 'home_page', component: Home },
  { id: 'about', label: 'About Page', dataKey: 'about_page', component: About },
  { id: 'auth', label: 'Auth Pages', dataKey: 'auth_pages', component: AuthPages },
  { id: 'team', label: 'Team Page', dataKey: 'team_page', component: Team },
  { id: 'blog', label: 'Blog Page', dataKey: 'blog_page', component: Blog },
  { id: 'service', label: 'Service Page', dataKey: 'service_page', component: Service },
  { id: 'contact', label: 'Contact Page', dataKey: 'contact_page', component: Contact },
  { id: 'Pricing', label: 'Pricing Page', dataKey: 'pricing_page', component: Pricing }
]

const activeTab = ref('primary')
const activeTabComponent = shallowRef(Primary)

const findTab = computed(() => tabs.find((tab) => tab.id === activeTab.value))
const switchTab = (tabId) => {
  activeTab.value = tabId
  activeTabComponent.value = findTab.value.component
}
</script>

<template>
  <div class="card-body card space-y-6">
    <div class="tabs tabs-vertical">
      <ul class="tabs-list w-72">
        <li v-for="tab in tabs" :key="tab.id" class="tabs-item">
          <button
            class="tabs-btn"
            :class="{ active: activeTab === tab.id }"
            @click="switchTab(tab.id)"
            :data-panel-id="'#' + tab.id"
            type="button"
          >
            <span>{{ trans(tab.label) }}</span>
          </button>
        </li>
      </ul>

      <div class="tabs-content h-[80vh]" data-simplebar>
        <div class="tabs-panel" :id="findTab.id" :class="{ active: activeTab === findTab.id }">
          <component :is="activeTabComponent" :data="props[findTab.dataKey]" />
        </div>
      </div>
    </div>
  </div>
</template>
