<script setup>
import { computed } from 'vue'

const props = defineProps(['meta'])

const previewLatitude = computed(() => props.meta.latitude || '0.000000')
const previewLongitude = computed(() => props.meta.longitude || '0.000000')
</script>

<template>
  <div>
    <img class="rounded-lg" src="/assets/images/location.png" alt="location" />
    <div class="p-1 leading-3">
      <p class="text-sm">{{ trans('Location Preview') }}</p>
      <p class="text-[10px] text-gray-400 underline">
        {{ trans('Latitude') }}: {{ previewLatitude }}, {{ trans('Longitude') }}:
        {{ previewLongitude }}
      </p>
    </div>
    <p class="text-end text-[10px] text-gray-400">{{ new Date().toLocaleTimeString() }}</p>
  </div>
</template>
