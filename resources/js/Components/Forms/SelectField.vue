<!-- @format -->

<template>
  <div class="w-full">
    <label v-if="label" class="label mb-1">
      {{ label }}
    </label>
    <select
      :value="modelValue"
      @input="updateInput"
      class="select"
      :class="`${setValidationMessage ? 'border border-danger-600 ' : ''} ${customClass}`"
    >
      <option v-if="placeholder" selected value="" disabled>{{ placeholder }}</option>
      <option v-for="item in options" :key="item?.id ?? item" :value="item?.id ?? item">
        {{ item?.name ?? item }}
      </option>
    </select>
    <div v-if="setValidationMessage" class="my-1 text-[11px] font-light text-danger-600">
      {{ setValidationMessage }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'SelectField',
  props: {
    label: {
      type: String,
      default: ''
    },
    modelValue: {
      type: [String, Number],
      default: ''
    },
    options: {
      type: Array,
      default: []
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
  },
  computed: {
    setValidationMessage: function () {
      if (this.validationMessage && Array.isArray(this.validationMessage)) {
        return this.validationMessage[0]
      }
      return this.validationMessage
    }
  },
  methods: {
    updateInput(event) {
      this.$emit('update:modelValue', event.target.value)
    }
  }
}
</script>
