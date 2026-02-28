<script setup>
import sharedComposable from '@/Composables/sharedComposable'

defineProps({
  assets: [Object, Array]
})

const { deleteRow } = sharedComposable()
</script>

<template>
  <div
    v-for="(asset, i) in assets"
    :key="i"
    class="relative h-60 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800"
  >
    <div class="dropdown absolute right-2 top-2 z-10" data-placement="bottom-end">
      <div class="dropdown-toggle p-1">
        <button
          type="button"
          class="flex h-8 w-8 items-center justify-center rounded-full bg-white font-medium shadow-md dark:bg-slate-800"
        >
          <Icon icon="bx:dots-vertical-rounded" />
        </button>
      </div>

      <div class="dropdown-content w-32 !overflow-visible p-1">
        <Link
          as="button"
          class="dropdown-link"
          @click="deleteRow(route('user.assets.destroy', asset.id))"
        >
          <Icon icon="bx:trash" />
          <span class="text-sm">{{ trans('Delete') }}</span>
        </Link>
      </div>
    </div>
    <template v-if="asset.mime_type.includes('image')">
      <img v-lazy="asset.path" class="h-52 w-full object-cover" alt="image" />
    </template>
    <template v-else-if="asset.mime_type.includes('video')">
      <video controls class="h-52 w-full">
        <source :src="asset.path" type="video/mp4" />
      </video>
    </template>
    <template v-else-if="asset.mime_type.includes('audio')">
      <audio controls class="h-52 w-full">
        <source :src="asset.path" type="audio/wav" />
        <source :src="asset.path" type="audio/mp3" />
      </audio>
    </template>
    <template v-else>
      <div class="flex h-full flex-col items-center justify-center">
        <Icon icon="bx:file-blank" class="h-6 w-6" />
        <p class="text-sm font-semibold capitalize">{{ asset.original_name }}</p>
      </div>
    </template>
    <div class="ml-auto mt-1 flex max-w-max items-center rounded-md px-1">
      <Icon icon="bx:file-blank" class="mr-1 h-4 w-4" />
      <span class="text-xs font-semibold capitalize">{{ asset.dynamic_file_size }}</span>
    </div>
  </div>
</template>
