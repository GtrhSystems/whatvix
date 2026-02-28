<script setup>
import DropzoneInput from '@/Components/Forms/DropzoneInput.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import IntersectionObserver from '@/Components/IntersectionObserver.vue'
import { useAssetStore } from '@/Store/assetStore'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()
const assetStore = useAssetStore()
</script>

<template>
  <Modal
    :header-state="true"
    header-title="Media Library"
    state="assetModal"
    state-key="assetModal"
    modal-size="w-8/12 2xl:w-6/12"
  >
    <div
      class="grid max-h-[60vh] w-full grid-cols-1 gap-2 overflow-y-auto sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
    >
      <div class="card flex items-center justify-center p-2">
        <DropzoneInput
          classes="h-32"
          v-model="assetStore.files"
          :accept="assetStore.events?.load ?? 'image'"
          :percentage="assetStore.assetUploadProgress"
          :instantSubmit="assetStore.assetSubmit"
          filePreviewPosition="bottom"
        />
      </div>

      <div
        v-for="(asset, index) in assetStore.allAssets.data"
        :key="asset.id + index"
        @click="assetStore.toggleSelect(asset)"
        class="h-36 w-full cursor-pointer rounded-md border bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800"
        :class="{
          'border-2 border-primary-600 dark:border-primary-600': assetStore.isSelected(asset),
          'border-1 dark:border-slate-700': !assetStore.isSelected(asset)
        }"
      >
        <template v-if="asset.mime_type.includes('image')">
          <img v-lazy="asset.path" class="h-full w-full object-contain" alt="image" />
        </template>
        <template v-else-if="asset.mime_type.includes('video')">
          <video controls class="h-full w-full">
            <source :src="asset.path" type="video/mp4" />
          </video>
        </template>
        <template v-else-if="asset.mime_type.includes('audio')">
          <audio controls class="h-full w-full">
            <source :src="asset.path" type="audio/wav" />
            <source :src="asset.path" type="audio/mp3" />
          </audio>
        </template>
        <template v-else>
          <div class="flex h-full flex-col items-center justify-center">
            <Icon icon="bx:file-blank" class="h-12 w-12" />
            <p class="text-sm font-semibold capitalize">
              {{ asset.original_name }}
            </p>
          </div>
        </template>
      </div>
    </div>
    <IntersectionObserver
      :observer-condition="assetStore.allAssets.loadMore"
      :after-intersection="() => assetStore.loadMoreAssets()"
      :loader="assetStore.allAssets.loading"
    />

    <div
      class="flex justify-end gap-2"
      v-if="assetStore.events.multiple && assetStore.selectedAssets.length"
    >
      <template v-if="assetStore.events.caption !== undefined">
        <input
          type="text"
          v-model="assetStore.events.caption"
          placeholder="caption (optional)"
          class="input w-full"
        />
      </template>
      <button
        v-if="assetStore.events.button !== undefined || assetStore.events.multiple"
        @click="assetStore.onSubmit"
        type="button"
        class="btn btn-primary"
      >
        {{ assetStore.events.button?.text ?? 'Attach' }}
      </button>
    </div>
  </Modal>
</template>
