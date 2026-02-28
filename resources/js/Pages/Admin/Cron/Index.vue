<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
defineOptions({ layout: AdminLayout })
defineProps(['jobs'])
</script>

<template>
  <div class="space-y-4">
    <div class="card-body card" v-for="job in jobs" :key="job.key">
      <div class="flex items-center gap-x-5">
        <h6 class="card-title">{{ job.title }}</h6>
        <p class="text-green-900 dark:text-primary-50">({{ job.time }})</p>
      </div>
      <div class="code-viewer mt-4 rounded-lg">
        <code> {{ job.url }}</code>
      </div>
    </div>
  </div>
</template>
