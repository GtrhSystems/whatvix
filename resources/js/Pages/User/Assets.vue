<script setup>
import { ref } from 'vue'

import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import AssetCard from '@/Components/User/AssetCard.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { router } from '@inertiajs/vue3'
import DropzoneInput from '@/Components/Forms/DropzoneInput.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['assets'])
const assetUploadProgress = ref(0)
const files = ref([])
const setFileType = (file) => {
  if (file.type.startsWith('image/')) {
    return 'image'
  } else if (file.type.startsWith('audio/')) {
    return 'audio'
  } else if (file.type.startsWith('video/')) {
    return 'video'
  } else {
    return 'document'
  }
}
const submit = () => {
  const modifiedFiles = files.value.map((file) => {
    return {
      file: file,
      type: setFileType(file)
    }
  })

  // return
  router.post(
    route('user.assets.store'),
    {
      files: modifiedFiles
    },
    {
      onProgress: (progress) => {
        assetUploadProgress.value = progress.percentage
      },
      onFinish: () => {
        setTimeout(() => (assetUploadProgress.value = 0), 1000)
      },
      onSuccess: () => {
        files.value = []
      }
    }
  )
}
const filterOptions = [
  {
    label: 'File Size',
    value: 'file_size'
  },
  {
    label: 'Media Type',
    value: 'file_type',
    options: [
      {
        label: 'Image',
        value: 'image'
      },
      {
        label: 'Video',
        value: 'video'
      },
      {
        label: 'Audio',
        value: 'audio'
      },
      {
        label: 'Document',
        value: 'document'
      }
    ]
  }
]
</script>

<template>
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">{{ trans('Assets') }}</h1>
    <FilterDropdown :options="filterOptions" :appends="{ active_tab: 'card' }" />
  </div>

  <div class="mt-8">
    <!-- card -->
    <div
      class="grid w-full grid-cols-1 gap-6 pb-8 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
    >
      <!-- cards -->

      <div class="card flex items-center justify-center p-3">
        <DropzoneInput
          classes="h-48"
          :percentage="assetUploadProgress"
          v-model="files"
          :instantSubmit="submit"
          multiple
        />
      </div>
      <AssetCard :assets="assets.data" />

      <Paginate class="col-span-full" :links="assets.links" />
    </div>
  </div>
  <NoDataFound v-if="assets.data?.length < 1" />
</template>
