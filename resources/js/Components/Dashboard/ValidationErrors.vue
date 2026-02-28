<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const errors = computed(() => usePage().props.errors)
const close = () => (usePage().props.errors = [])
const hasErrors = computed(() => Object.keys(errors.value).length > 0)
</script>

<template>
  <transition name="slide-right">
    <div v-if="hasErrors" class="validation-errors">
      <div class="flex justify-end">
        <button class="btn btn-danger btn-sm" @click="close">X</button>
      </div>
      <div class="flex flex-col">
        <p v-for="(error, key) in errors" :key="key">* {{ error.replace(/[.,]/g, ' ') }}</p>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.validation-errors {
  position: fixed;
  right: 2rem;
  border: rgba(255, 0, 0, 0.2) solid 1px;
  padding: 1rem;
  background-color: white;
  z-index: 999999;
  border-radius: 5px;
  width: 25rem;
  box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
}
.dark .validation-errors {
  background-color: rgb(31, 41, 55);
}
.validation-errors div p {
  margin-top: 1rem;
  color: rgb(255, 0, 0);
}

.slide-right-enter-active,
.slide-right-leave-active {
  transition-duration: 0.4s;
  transition-property: opacity, transform;
  transition-timing-function: ease;
}

.slide-right-enter-from,
.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
