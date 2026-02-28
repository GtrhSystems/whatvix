<template>
  <div class="flex min-w-72 flex-col items-center">
    <div ref="sliderRef" class="relative h-2 w-full rounded-lg bg-gray-200">
      <!-- Indicator -->
      <div
        class="absolute h-2 rounded-lg bg-primary-600"
        :style="{
          left: `${getLeftPosition(internalValue[0])}%`,
          width: `${getRangeWidth()}%`
        }"
      />
      <!-- Left  -->
      <button
        type="button"
        class="absolute top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-dark-600"
        :style="{ left: `${getLeftPosition(internalValue[0])}%` }"
        @mousedown="startDrag('left', $event)"
        @touchstart="startDrag('left', $event)"
      />
      <!-- Right -->
      <button
        type="button"
        class="absolute top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-dark-600"
        :style="{ left: `${getLeftPosition(internalValue[1])}%` }"
        @mousedown="startDrag('right', $event)"
        @touchstart="startDrag('right', $event)"
      />
    </div>
    <div class="mt-1.5 flex w-full justify-between text-sm">
      <span class="-translate-x-1">{{ internalValue[0] }}</span>
      <span class="translate-x-1">{{ internalValue[1] }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [10, 1000]
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: 100
  },
  step: {
    type: Number,
    default: 1
  }
})

const emit = defineEmits(['update:modelValue'])

const internalValue = ref([...props.modelValue])
const dragging = ref(null)
const sliderRef = ref(null)

watch(
  () => props.modelValue,
  (newValue) => {
    internalValue.value = [...newValue]
  },
  { deep: true }
)

const getLeftPosition = (value) => {
  return ((value - props.min) / (props.max - props.min)) * 100
}

const getRangeWidth = () => {
  const width = getLeftPosition(internalValue.value[1]) - getLeftPosition(internalValue.value[0])
  return Math.min(width, 100)
}

const startDrag = (thumb, event) => {
  event.preventDefault()
  dragging.value = thumb
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
  document.addEventListener('touchmove', onDrag)
  document.addEventListener('touchend', stopDrag)
}

const stopDrag = () => {
  dragging.value = null
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
  document.removeEventListener('touchmove', onDrag)
  document.removeEventListener('touchend', stopDrag)
}

const onDrag = (event) => {
  if (!dragging.value || !sliderRef.value) return

  const slider = sliderRef.value
  const rect = slider.getBoundingClientRect()
  const clientX = event.touches ? event.touches[0].clientX : event.clientX
  const percentage = Math.min(Math.max((clientX - rect.left) / rect.width, 0), 1)
  const value =
    Math.round(props.min + (percentage * (props.max - props.min)) / props.step) * props.step

  if (dragging.value === 'left') {
    if (value < internalValue.value[1] - props.step) {
      internalValue.value[0] = Math.max(value, props.min)
    }
  } else if (dragging.value === 'right') {
    if (value > internalValue.value[0] + props.step) {
      internalValue.value[1] = Math.min(value, props.max)
    }
  }

  emit('update:modelValue', [...internalValue.value])
}

onMounted(() => {
  document.addEventListener('mouseup', stopDrag)
  document.addEventListener('touchend', stopDrag)
})

onBeforeUnmount(() => {
  document.removeEventListener('mouseup', stopDrag)
  document.removeEventListener('touchend', stopDrag)
})
</script>
