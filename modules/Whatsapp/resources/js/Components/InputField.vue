<script setup>
import { computed, defineProps, defineEmits } from 'vue'

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
  maxLength: {
    type: [Number, String]
  }
})

const emit = defineEmits(['update:modelValue'])

const updateInput = (event) => {
  emit('update:modelValue', event.target.value)
}

const maxLengthError = computed(() => {
  if (props.maxLength) {
    return props.modelValue?.length > props.maxLength
  }
  return false
})

</script>

<template>
  <div class="w-full" :class="customStyle">
    <label v-if="label" class="label mb-1 capitalize">
      {{ trans(label) }}
    </label>
    <input :type="type" :value="modelValue" @input="updateInput" class="input" :class="{
      'border border-danger-600': validationMessage,
      'border-b border-danger-600': maxLengthError
    }" :placeholder="placeholder" :required="isRequired" :step="step" />
    <div class="mt-1 flex items-center" v-if="validationMessage || maxLength" :class="{
      'justify-between': validationMessage,
      'justify-end': !validationMessage
    }">
      <div v-if="validationMessage" class="my-1 text-sm font-light text-danger-600">
        {{ validationMessage }}
      </div>
      <div v-if="maxLength" class="text-sm flex gap-1">
        <span :class="[props.modelValue?.length > props.maxLength ? 'text-danger-600' : 'text-green-600']">
          {{ modelValue?.length ?? 0 }}</span>
        <span>/</span>
        <span>{{ props.maxLength }}</span>
      </div>
    </div>
  </div>
</template>
