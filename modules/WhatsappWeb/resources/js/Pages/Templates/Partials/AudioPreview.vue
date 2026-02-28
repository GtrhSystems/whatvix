<script setup>
import { computed } from 'vue'

const props = defineProps(['meta'])

const audioUrl = computed(() => props.meta?.audioUrl || props.meta?.audio)
</script>

<template>
  <div class="rounded-lg bg-white p-2 dark:bg-dark-700">
    <div class="flex items-end justify-between gap-2">
      <div class="flex h-8 w-6 items-center justify-center">
        <Icon icon="hugeicons:download-circle-01" class="text-2xl text-gray-400" />
      </div>
      <div class="flex-1">
        <audio v-if="audioUrl" :src="audioUrl" controls class="h-8 w-full"></audio>
        <template v-else>
          <hr class="h-1 w-full bg-gray-300" />
          <div class="flex justify-between text-[10px] text-gray-400">
            <span>0:00</span>
            <span>{{ new Date().toLocaleTimeString() }}</span>
          </div>
        </template>
      </div>
      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-white">
        <Icon icon="bi:music-note-beamed" class="mr-0.5" />
      </div>
    </div>
  </div>
</template>
