<!-- @format -->

<template>
<div class="w-full" :class="customStyle">
    <label v-if="label" class="label mb-1">
        {{ trans(label) }}
    </label>
    <input
        :type="type"
        :value="modelValue"
        @input="updateInput"
        class="input"
        :class="{ 'border border-danger-600': setValidationMessage }"
        :placeholder="placeholder"
        :required="isRequired"
    />
    <div
        v-if="setValidationMessage"
        class="my-1 text-[11px] font-light text-danger-600"
    >
        {{ setValidationMessage }}
    </div>
</div>
</template>

<script>
export default {
    name: "InputField",
    props: {
        label: {
            type: String,
            default: "",
        },
        customStyle: {
            type: String,
            default: "",
        },
        modelValue: {
            type: [String, Number],
            default: "",
        },
        type: {
            type: String,
            default: "text",
        },
        validationMessage: {
            type: [String, Array],
            default: "",
        },
        placeholder: {
            type: String,
            default: "Example: text...",
        },
        isRequired: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        setValidationMessage: function () {
            if (this.validationMessage && Array.isArray(this.validationMessage)) {
                return this.validationMessage[0];
            }
            return this.validationMessage;
        }
    },
    methods: {
        updateInput(event) {
            this.$emit("update:modelValue", event.target.value);
        },
    },
};
</script>
