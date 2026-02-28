<script setup>
import { defineAsyncComponent } from 'vue'

const props = defineProps(['template'])

const AudioPreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/AudioPreview.vue')
)

const ImagePreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/ImagePreview.vue')
)

const LocationPreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/LocationPreview.vue')
)

const TextPreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/TextPreview.vue')
)

const VideoPreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/VideoPreview.vue')
)
const PollPreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/PollPreview.vue')
)
const DocumentPreview = defineAsyncComponent(() =>
  import('@whatsappWeb/Pages/Templates/Partials/DocumentPreview.vue')
)

const previewComponents = {
  text: TextPreview,
  image: ImagePreview,
  video: VideoPreview,
  audio: AudioPreview,
  location: LocationPreview,
  poll: PollPreview,
  document: DocumentPreview
}
</script>

<template>
  <component :is="previewComponents[template?.type ?? 'text']" :meta="template?.meta ?? []" />
</template>
