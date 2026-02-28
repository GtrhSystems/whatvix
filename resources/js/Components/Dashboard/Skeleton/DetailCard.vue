<script setup>
defineProps({
  classes: {
    type: String,
    default: 'mb-5 h-32 p-4'
  }
})
</script>
<template>
  <div class="card flex animate-pulse flex-col justify-center bg-gray-300" :class="classes">
    <div class="h-6 w-1/3 rounded bg-gray-400"></div>
    <div class="mt-2 h-4 w-2/3 rounded bg-gray-400"></div>
  </div>
</template>
