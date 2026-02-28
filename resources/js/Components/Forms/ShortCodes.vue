<script setup>
const props = defineProps({
  shortCodes: {
    type: Object,
    default: ['{name}']
  },
  modelValue: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const updateModelValue = (code) => {
  emit('update:modelValue', props.modelValue ? (props.modelValue += ` ${code}`) : `${code}`)
}
</script>

<template>
  <div class="flex flex-row items-center gap-3 md:gap-4">
    <template v-for="code in props.shortCodes" :key="code">
      <button
        type="button"
        class="btn bg-slate-300 text-xs text-gray-700 hover:bg-opacity-60 dark:bg-dark-700 dark:text-white dark:hover:bg-opacity-60"
        @click="updateModelValue(code)"
      >
        {{ code }}
      </button>
    </template>
  </div>
</template>
