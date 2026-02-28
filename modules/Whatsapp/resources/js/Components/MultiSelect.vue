<template>
  <div class="w-full">
    <label v-if="label" class="label mb-1">
      {{ label }}
    </label>
    <Multiselect
      :modelValue="modelValue"
      @update:modelValue="updateInput"
      :class="setValidationMessage ? 'border border-danger-600' : ''"
      mode="tags"
      :close-on-select="false"
      :searchable="true"
      :create-option="true"
      :options="options"
      :placeholder="placeholder"
      class="multiselect-dark"
    />
    <div v-if="setValidationMessage" class="my-1 text-[11px] font-light text-danger-600">
      {{ setValidationMessage }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Multiselect from '@vueform/multiselect'

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: Array,
    default: () => []
  },
  options: {
    type: Array,
    default: () => []
  },
  validationMessage: {
    type: [String, Array],
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select'
  },
  customClass: {
    type: String,
    default: ''
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
  emit('update:modelValue', event)
}
</script>
