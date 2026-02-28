<script setup>
import { onMounted, ref, watch } from 'vue'
import HollowDotsSpinner from './HollowDotsSpinner.vue'

const props = defineProps({
  observerCondition: { type: Boolean, default: true },
  loader: { type: Boolean, default: false },
  afterIntersection: { type: Function, default: () => {} },
  intersectionStart: { type: Number, default: 0 }
})

const intersectionTargetView = ref(null)
let observer = null
let intersectionCount = 0

const onIntersection = (entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting && props.observerCondition) {
      intersectionCount++
      if (intersectionCount > props.intersectionStart) {
        props.afterIntersection()
      }
    }
  })
}
watch(
  () => props.observerCondition,
  () => {
    if (props.observerCondition) {
      setObserver()
    }
  }
)

onMounted(() => {
  setObserver()
})

const setObserver = () => {
  observer = new IntersectionObserver(onIntersection, {
    root: null,
    rootMargin: '40px',
    threshold: 0.1
  })
  observer.observe(intersectionTargetView.value)
}
</script>

<template>
  <div ref="intersectionTargetView" class="h-2"></div>
  <div class="mt-5 flex justify-center" v-if="loader">
    <HollowDotsSpinner />
  </div>
</template>
