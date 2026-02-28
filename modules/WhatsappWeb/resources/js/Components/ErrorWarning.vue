<template>
  <div
    v-if="errorCode && errorData[errorCode]"
    class="mb-3 mt-8 flex flex-col items-center justify-center"
  >
    <img :src="errorData[errorCode]?.image" class="mb-1 h-20" alt="" />
    <p class="font-semibold text-gray-500 dark:text-gray-300">
      {{ errorData[errorCode]?.message }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  errorCode: {
    type: Number,
    default: null
  }
})

const errorData = {
  500: {
    message: 'Server Error',
    image: '/assets/images/server-warning.png'
  }
}
</script>
