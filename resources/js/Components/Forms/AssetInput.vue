<script setup>
import { computed } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'
import { useAssetStore } from '@/Store/assetStore'

const assetStore = useAssetStore()
const { textExcerpt } = sharedComposable()

const model = defineModel({
  type: [Array, String],
  default: () => []
})

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'image'
  },
  multiple: {
    type: Boolean,
    default: false
  }
})

const openAssetModal = () => {
  assetStore.openModal({
    load: props.type,
    multiple: props.multiple,
    onOpen: () => {},
    onSelect: (asset) => {
      if (!props.multiple) {
        model.value = asset.path
        assetStore.closeModal()
      }
    },
    onSubmit: (assets) => {
      model.value = assets.map((asset) => asset.path)
    }
  })
}

const modelIsArray = computed(() => Array.isArray(model.value))

const unSelect = (path) => {
  if (modelIsArray.value) {
    model.value = model.value.filter((asset) => asset.path !== path)
  } else {
    model.value = null
  }
}
</script>
<template>
  <label for=""> {{ label }}</label>
  <div class="relative">
    <div>
      <div
        class="card my-1 flex max-w-full flex-wrap gap-2 rounded-md border bg-black/50 bg-slate-100 p-1 dark:border-slate-700"
      >
        <div class="relative h-16 w-16 rounded-md" v-if="model">
          <div class="h-full w-full">
            <img
              v-if="type === 'image'"
              :src="model"
              class="h-full w-full rounded object-cover"
              alt="img"
            />
            <video
              v-else-if="type === 'video'"
              :src="model"
              class="h-full w-full rounded object-cover"
            ></video>
            <Icon
              v-else-if="type === 'audio'"
              :src="model"
              icon="bx:microphone"
              class="h-12 w-12"
            />
            <Icon
              v-else
              icon="bx:file"
              class="absolute right-0 top-0 z-10 h-full w-full p-1 hover:bg-black/50"
            />
            <Icon
              icon="bx:x"
              @click="unSelect(model)"
              class="absolute right-0 top-0 z-10 h-4 w-4 cursor-pointer rounded hover:bg-red-600"
            >
            </Icon>
          </div>
        </div>
        <button
          type="button"
          class="flex h-16 w-16 items-center justify-center rounded border dark:border-slate-500"
          @click="openAssetModal"
        >
          <Icon icon="bx:upload" class="text-2xl text-slate-500 dark:text-slate-400" />
        </button>
      </div>
    </div>
  </div>
</template>
