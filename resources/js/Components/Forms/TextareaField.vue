<template>
  <div class="w-full" :class="customStyle">
    <label v-if="label" class="label mb-1">
      {{ trans(label) }}
    </label>
    <textarea
      :type="type"
      :value="modelValue"
      @input="updateInput"
      class="input"
      :class="{ 'border border-danger-600': setValidationMessage }"
      :placeholder="placeholder"
      :required="isRequired"
      :step="step"
      v-bind="attrs"
    />
    <div v-if="setValidationMessage" class="my-1 text-[11px] font-light text-danger-600">
      {{ setValidationMessage }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  customStyle: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  validationMessage: {
    type: [String, Array],
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Example: text...'
  },
  isRequired: {
    type: Boolean,
    default: false
  },
  step: {
    type: String,
    default: 'any'
  },
  attrs: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const setValidationMessage = computed(() => {
  if (props.validationMessage && Array.isArray(props.validationMessage)) {
    return props.validationMessage[0]
  }
  return props.validationMessage
})

const updateInput = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>
