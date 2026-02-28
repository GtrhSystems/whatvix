<template>
  <div class="w-full">
    <label v-if="label" class="label mb-1">
      {{ label }}
    </label>
    <Multiselect
      class="multiselect-dark"
      :modelValue="modelValue"
      @update:modelValue="emit('update:modelValue', $event)"
      :class="validationMessage ? 'border border-danger-600' : ''"
      :mode="mode"
      :close-on-select="false"
      :searchable="true"
      :create-option="true"
      :options="options"
      :placeholder="placeholder"
      :label="inputLabel"
      :value-prop="valueProp"
    />

    <div v-if="validationMessage" class="my-1 text-[11px] font-light text-danger-600">
      {{ Array.isArray(validationMessage) ? validationMessage[0] : validationMessage }}
    </div>
  </div>
</template>

<script setup>
import Multiselect from '@vueform/multiselect'

const emit = defineEmits(['update:modelValue'])
const props = defineProps({
  inputLabel: {
    type: String,
    default: 'label'
  },
  label: {
    type: String,
    default: ''
  },
  mode: {
    type: String,
    default: 'tags'
  },
  modelValue: {
    type: [Array, Object, String, Number],
    default: () => []
  },
  options: {
    type: [Array, Object],
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
  },
  valueProp: {
    type: String,
    default: 'value'
  }
})
</script>
