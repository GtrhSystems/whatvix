<script setup>
import sharedComposable from '@/Composables/sharedComposable'
import { computed } from 'vue'

const props = defineProps(['meta'])

const previewDocument = computed(
  () => props.meta?.document_name || props.meta?.document || 'document'
)
const previewCaption = computed(() => props.meta.caption || 'Your caption will appear here')
const getExtension = (filename) => {
  const parts = filename.split('.')
  return parts[parts.length - 1]
}
const { textExcerpt } = sharedComposable()
</script>

<template>
  <div>
    <div class="card grid w-full grid-cols-8 p-1.5">
      <Icon icon="bx:file" class="col-span-1 text-lg" />
      <div class="col-span-6 flex flex-col items-start justify-start leading-4">
        <p class="-mt-0.5 text-left text-sm">{{ textExcerpt(previewDocument, 20) }}</p>
        <p class="text-left">
          <span class="text-[10px] text-gray-400">
            {{ new Date().toLocaleTimeString() }}
          </span>
          .
          <span class="text-end text-[10px] text-gray-400">
            <template v-if="props.meta.document">
              {{ getExtension(props.meta.document_name || props.meta.document) }}
            </template>
            <template v-else> {{ trans('html') }} </template>
          </span>
        </p>
      </div>
      <Icon icon="bx:download" class="col-span-1 text-lg" />
    </div>

    <p class="mt-1 p-1 text-sm leading-3">{{ previewCaption }}</p>
  </div>
</template>
