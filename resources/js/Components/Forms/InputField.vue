<script setup>
import InputFieldError from '@/Components/Forms/InputFieldError.vue'
defineOptions({
  inheritAttrs: false
})
const model = defineModel()
const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  subLabel: {
    type: String,
    default: null
  },
  subLabelClass: {
    type: String,
    default: 'text-primary-500'
  },
  classes: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  prefixIcon: {
    type: String,
    default: null
  },
  prefixIconType: {
    type: String,
    default: 'icon'
  },
  error: {
    type: String,
    default: null
  },
  placeholder: {
    type: String,
    default: ''
  },
  step: {
    type: String,
    default: 'any'
  }
})

const emit = defineEmits(['update:modelValue'])
</script>
<template>
  <div class="w-full" :class="classes">
    <label v-if="label" class="label mb-1 capitalize">
      {{ trans(label) }}
      <span v-if="subLabel" class="text-xs" :class="subLabelClass">( {{ trans(subLabel) }} )</span>
    </label>
    <div class="flex items-center">
      <div
        v-if="prefixIcon"
        class="flex h-[38px] w-9 items-center justify-center rounded-md rounded-r-none bg-dark-100 dark:bg-dark-700"
      >
        <Icon v-if="prefixIconType == 'icon'" :icon="prefixIcon" class="text-base" />
        <template v-else>
          {{ prefixIcon }}
        </template>
      </div>
      <input
        v-bind="$attrs"
        :type="type"
        :value="modelValue"
        v-model="model"
        class="input"
        :class="{ 'border border-danger-600': error, 'rounded-l-none': prefixIcon }"
        :placeholder="placeholder"
        :step="step"
      />
    </div>
    <InputFieldError :message="error" />
  </div>
</template>
