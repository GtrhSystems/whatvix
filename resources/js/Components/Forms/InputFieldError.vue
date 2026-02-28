<script setup>
const props = defineProps({
  message: String
})
</script>

<template>
  <span v-show="message?.length" class="mt-1 block text-red-500 text-danger text-sm">
    {{ message }}
  </span>
</template>
