<script setup>
import InputError from '@/Components/InputFieldError.vue'
import sharedComposable from '@/Composables/sharedComposable'
import { ref, computed } from 'vue'
const props = defineProps({
  modelValue: {
    type: [Array, String],
    default: () => []
  },
  accept: {
    type: String,
    default: 'default'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxFiles: {
    type: Number,
    default: 10
  },
  percentage: {
    type: Number,
    default: 0
  },
  instantSubmit: Function,
  classes: {
    type: String,
    default: 'min-h-28'
  },
  label: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: null
  },
  labelClass: {
    type: String,
    default: ''
  },
  required: {
    type: [Boolean, String],
    default: false
  },
  uploadedFiles: {
    type: Array,
    default: () => []
  },
  error: {
    type: String,
    default: null
  },
  filePreviewPosition: {
    type: String,
    default: 'top'
  }
})
const { textExcerpt } = sharedComposable()
const emit = defineEmits(['update:modelValue'])
const fileInput = ref(null)
const isDragging = ref(false)
const isNewFileAdded = ref(false)
const showUploadedFiles = ref(false)
const fileMessage = ref('No Files Selected')
const fileError = ref(props.error)
const acceptInputs = {
  image: 'image/*',
  video: 'video/*',
  audio: 'audio/*',
  document: '.pdf,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
  documents: '.pdf,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
  pdf: '.pdf',
  csv: '.csv',
  doc: '.doc,.docx',
  xls: '.xls,.xlsx',
  ppt: '.ppt,.pptx',
  default: '*/*'
}

const acceptTypes = computed(() =>
  props.accept
    .split(',')
    .map((accept) => acceptInputs[accept])
    .join(',')
)

const onFileChange = () => {
  console.log(fileInput.value.files)
  if (fileInput.value.files.length) {
    isNewFileAdded.value = true
    updateDropzoneFileList()
  }
}

const updateFileMessage = (files) => {
  if (files.length === 1) {
    fileMessage.value = files[0].name
  } else if (files.length > 1) {
    fileMessage.value = `${files.length} files selected`
  } else {
    fileMessage.value = 'No file selected'
  }
}

const updateDropzoneFileList = () => {
  const newFiles = Array.from(fileInput.value.files)
  const maxFiles = props.multiple ? props.maxFiles : 1
  const currentFiles = props.modelValue || []

  const acceptType = props.accept || 'default'
  const allowedExtensions = acceptInputs[acceptType].split(',')

  const invalidFiles = newFiles.filter((file) => {
    const fileExt = '.' + file.name.split('.').pop().toLowerCase()
    return !(
      allowedExtensions.includes('*/*') ||
      allowedExtensions.some((ext) =>
        ext.includes('/*') ? file.type.startsWith(ext.split('/')[0]) : ext.toLowerCase() === fileExt
      )
    )
  })

  if (invalidFiles.length) {
    const invalidNames = invalidFiles.map((file) => file.name).join(', ')
    fileError.value = `Invalid files: ${invalidNames}. Supported files: ${acceptInputs[acceptType]}`
    return
  }

  const combinedFiles = [...currentFiles, ...newFiles]
  const finalFiles = combinedFiles.slice(-maxFiles)

  emit('update:modelValue', finalFiles)
  updateFileMessage(finalFiles)

  if (props.instantSubmit) {
    props.instantSubmit()
  }
}

const setFileURL = (file) => {
  return URL.createObjectURL(file)
}

const clearFile = (i) => {
  const files = [...props.modelValue]
  const removedFile = files.splice(i, 1)[0]
  emit('update:modelValue', files)
  updateFileMessage(files)
  if (removedFile) {
    URL.revokeObjectURL(removedFile)
  }
  if (files.length === 0) {
    fileInput.value.value = ''
    showUploadedFiles.value = false
    isNewFileAdded.value = false
  }
}
</script>

<template>
  <div class="relative w-full">
    <label
      v-if="label"
      :for="label"
      class="mb-1 block text-sm capitalize dark:text-dark-200"
      :class="labelClass"
    >
      {{ label }}
      <span v-if="required" class="scale-110 text-red-500">*</span>
    </label>
    <div
      class="dropzone-area relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-dark-400 p-4 text-center dark:text-primary-50"
      :class="[{ 'bg-blue-50 dark:bg-blue-800': isDragging }, classes]"
    >
      <div
        @dragover.prevent="isDragging = true"
        @dragleave="isDragging = false"
        @dragend="isDragging = false"
        class="flex flex-col items-center justify-center"
      >
        <div class="flex items-center gap-2">
          <Icon icon="bx:upload" class="mb-1 h-5 w-5" />
          <p class="text-xs font-semibold">Click or drag & drop</p>
        </div>
        <small class="scale-75 text-xs" v-if="multiple">Max ({{ maxFiles }} files)</small>

        <p class="mt-2 line-clamp-1 text-sm">
          {{ modelValue?.length > 0 ? textExcerpt(fileMessage, 20) : 'No file selected' }}
        </p>
      </div>
      <input
        type="file"
        :multiple="multiple"
        :accept="acceptTypes"
        ref="fileInput"
        @input="onFileChange"
        aria-label="Upload files"
      />

      <div v-if="percentage > 0" class="absolute bottom-2 mx-auto w-11/12">
        <div
          :style="{ width: `${percentage}%` }"
          class="h-3 w-1/2 rounded-full border border-dashed border-white bg-primary-500 transition-width"
        >
          <p class="translate-y-[-10.5px] whitespace-nowrap">
            <span class="text-[10px] text-white">{{ percentage }} %</span>
          </p>
        </div>
      </div>
      <button
        type="button"
        v-if="modelValue?.length > 0 && !showUploadedFiles && isNewFileAdded"
        class="absolute right-2 top-1 text-xs underline"
        @click="showUploadedFiles = true"
      >
        <span>Show files</span>
      </button>
    </div>
    <p v-if="subtitle" class="mt-1 text-xs dark:text-gray-300">*{{ subtitle }}</p>
    <InputError v-if="fileError" :message="fileError" />
    <template v-if="modelValue?.length > 0 && showUploadedFiles">
      <div
        class="styled-scrollbar card absolute left-0 z-50 mt-2 flex w-full flex-wrap gap-2 rounded-md bg-primary-200 p-2 dark:bg-dark-700/95 dark:text-primary-50"
        :class="{
          'h-[25rem] overflow-y-auto': modelValue.length > 4,
          'bottom-full': filePreviewPosition === 'top',
          'top-full': filePreviewPosition === 'bottom'
        }"
      >
        <div
          class="flex w-full justify-between border-b border-primary-300 py-1 dark:border-primary-600"
        >
          <p class="text-xs font-semibold">Uploaded Files</p>
          <button
            @click="showUploadedFiles = false"
            type="button"
            class="flex items-center justify-center text-red-500 hover:underline"
          >
            <Icon icon="bx:x" />
            <span class="text-xs">Close</span>
          </button>
        </div>
        <section
          class="relative h-32 min-w-32 flex-1 rounded-md border border-slate-400 dark:border-slate-500"
          v-for="(file, i) in modelValue"
          :key="i"
        >
          <button
            @click="clearFile(i)"
            type="button"
            class="absolute right-1 top-1 z-50 flex h-6 w-6 items-center justify-center rounded-full border border-red-500 text-red-500 hover:bg-red-500/10"
          >
            <Icon icon="bx:x" />
          </button>

          <img
            v-if="file.type.startsWith('image/')"
            :src="setFileURL(file)"
            class="h-full w-full rounded object-contain"
            alt="img"
            loading="lazy"
          />
          <video
            v-else-if="file.type.startsWith('video/')"
            class="h-full w-full rounded object-contain"
            :src="setFileURL(file)"
            width="100%"
            height="100%"
            controls
            loading="lazy"
          ></video>
          <div
            class="flex h-full w-full items-center justify-center"
            v-else-if="file.type.startsWith('audio/')"
          >
            <audio :src="setFileURL(file)" class="h-14 w-full" controls></audio>
          </div>
          <div
            v-else
            class="flex h-full items-center justify-center gap-1 rounded bg-white p-2 text-center text-xs text-black"
          >
            <Icon icon="bx:file" class="min-w-5 text-lg" />
            <span class="line-clamp-1"> {{ file.name }}</span>
          </div>
        </section>
      </div>
    </template>
  </div>

  <template
    v-if="(uploadedFiles?.length || Object.keys(uploadedFiles ?? {})?.length) && !isNewFileAdded"
  >
    <div class="flex flex-wrap">
      <template v-if="Array.isArray(uploadedFiles)">
        <a
          class="w-16"
          :href="uploadedFile.path"
          target="_blank"
          v-for="uploadedFile in uploadedFiles"
          :key="uploadedFile.id"
        >
          <img class="rounded-md" :src="uploadedFile.path" alt="voucher" />
        </a>
      </template>
      <template v-else>
        <a class="w-16" :href="uploadedFiles.path" target="_blank">
          <img class="rounded-md" :src="uploadedFiles.path" alt="voucher" />
        </a>
      </template>
    </div>
  </template>
</template>

<style scoped>
.dropzone-area [type='file'] {
  cursor: pointer;
  position: absolute;
  opacity: 0;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>
