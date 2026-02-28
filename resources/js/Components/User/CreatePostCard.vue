<script setup>
defineProps({
  classes: {
    type: String,
    default: 'h-96 min-w-[18rem]'
  },
  text: {
    type: String,
    default: 'Create Post'
  },
  percentage: {
    type: Number,
    default: 0
  }
})
const emit = defineEmits(['action'])
</script>

<template>
  <button
    @click="emit('action')"
    :class="classes"
    class="relative flex flex-col items-center justify-center rounded-2xl border border-dashed border-primary-200 bg-primary-400/10 dark:bg-primary-700/10"
  >
    <div
      class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-primary-400 bg-opacity-10 text-primary-500"
    >
      <Icon icon="bx:plus" />
    </div>
    <p class="mt-1">
      <span class="text-sm">{{ percentage > 0 ? trans('Uploading...') : text }}</span>
    </p>
    <div v-if="percentage > 0" class="absolute bottom-2 mx-auto w-11/12">
      <div
        :style="{ width: `${percentage}%` }"
        class="h-3 w-1/2 rounded-full border border-dashed border-white bg-primary-500 transition-width"
      >
        <p class="translate-y-[-10.5px] whitespace-nowrap">
          <span class="text-[10px]">{{ percentage }} %</span>
        </p>
      </div>
    </div>
  </button>
</template>
