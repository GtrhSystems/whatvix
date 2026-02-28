<script setup>
defineProps({
  skeletonCount: {
    type: Number,
    default: 4
  }
})
</script>

<template>
  <section class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
    <div v-for="n in skeletonCount" :key="n" class="card animate-pulse">
      <div class="card-body flex items-center gap-2">
        <div
          class="h-10 w-10 rounded-full bg-dark-600 bg-opacity-20 p-3 text-xl dark:bg-dark-300"
        ></div>
        <div class="flex flex-1 flex-col gap-2">
          <div class="h-3 w-28 rounded bg-dark-300"></div>
          <div class="h-4 w-16 rounded bg-dark-400"></div>
        </div>
      </div>
    </div>
  </section>
</template>
