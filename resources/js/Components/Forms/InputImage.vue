<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: null
  }
})

const model = defineModel()

const previewUrl = computed(() => {
  if (model.value instanceof File) {
    return URL.createObjectURL(model.value)
  }
  return model.value
})
</script>

<template>
  <div class="rounded border p-2 dark:border-gray-600">
    <label class="mb-2 block text-sm capitalize dark:text-dark-200">{{ label }}</label>
    <div class="flex items-center justify-between gap-2 rounded p-1">
      <input
        type="file"
        @change="(e) => (model = e.target.files[0])"
        accept="image"
        class="input"
      />
      <img v-if="previewUrl" :src="previewUrl" class="mt-1 h-9 rounded" />
      <button class="btn btn-danger" v-if="model" type="button" @click="model = null">X</button>
    </div>
    <span v-if="error" class="text-xs text-red-500">{{ error }}</span>
  </div>
</template>
