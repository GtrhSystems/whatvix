<script setup>
const props = defineProps({
  modelValue: String,
  codes: {
    type: Array,
    default: ['{name}', '{phone}']
  }
})
const emit = defineEmits(['update:modelValue'])
const setShortCode = (shortCode) => {
  emit('update:modelValue', `${props.modelValue} ${shortCode}`)
}
</script>

<template>
  <div class="flex flex-row items-center gap-3 py-2">
    <button
      v-for="code in codes"
      @click="setShortCode(code)"
      :key="code"
      type="button"
      class="btn bg-slate-200 text-gray-700 hover:opacity-90 dark:bg-dark-700 dark:text-white"
    >
      {{ code }}
    </button>
  </div>
</template>
