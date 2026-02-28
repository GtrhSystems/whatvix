<script setup>
import { onMounted, onUnmounted, ref } from 'vue'

const isSticky = ref(false)

const handleSticky = () => {
  if (window.scrollY > 200) {
    isSticky.value = true
  } else {
    isSticky.value = false
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleSticky)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleSticky)
})

const scrollTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <button class="scroll-top" @click="scrollTop">
    <i class="bi bi-arrow-up-short"></i>
  </button>
</template>
