<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import DotLoader from '@/Components/DotLoader.vue'
import { useChatStore } from '@/Store/chatStore'
import RecordRTC from 'recordrtc'

const props = defineProps({
  store: {
    type: Object,
    required: true
  }
})

const chatStore = useChatStore()

const audio = ref({
  blob: null,
  url: null,
  file: null,
  isRecording: false,
  duration: 0
})

const isDeviceSupportAudioInput = ref(true)
const recorder = ref(null)
const timerInterval = ref(null)

const formattedTimer = computed(() => {
  const minutes = Math.floor(audio.value.duration / 60)
  const seconds = audio.value.duration % 60
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

const startRecording = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })

    // Initialize RecordRTC
    recorder.value = new RecordRTC(stream, { type: 'audio' })

    // Start recording
    recorder.value.startRecording()
    audio.value.isRecording = true
    audio.value.duration = 0

    // Start timer
    timerInterval.value = setInterval(() => {
      audio.value.duration++
    }, 1000)
  } catch (error) {
    console.error('Error accessing microphone:', error)
    isDeviceSupportAudioInput.value = false
    audio.value.isRecording = false
  }
}

const stopRecording = () => {
  if (!recorder.value) return

  recorder.value.stopRecording(() => {
    audio.value.blob = recorder.value.getBlob()
    audio.value.url = URL.createObjectURL(audio.value.blob)

    // Clean up
    if (timerInterval.value) {
      clearInterval(timerInterval.value)
    }

    try {
      // Safely try to stop all tracks
      const stream = recorder.value.stream
      if (stream && stream.getTracks) {
        stream.getTracks().forEach((track) => track.stop())
      }
    } catch (err) {
      console.warn('Could not stop media tracks:', err)
    } finally {
      audio.value.isRecording = false
      recorder.value.destroy()
      recorder.value = null
    }
  })
}

const deleteRecord = () => {
  if (audio.value.url) {
    URL.revokeObjectURL(audio.value.url)
  }
  audio.value.url = null
  audio.value.blob = null
  audio.value.file = null
  audio.value.isRecording = false
  audio.value.duration = 0
}

const closeRecording = () => {
  stopRecording()
  deleteRecord()
  chatStore.inputMessage.type = 'text'
}

const sendMessage = () => {
  chatStore.inputMessage.type = 'voice'
  chatStore.inputMessage.file = audio.value.blob
  chatStore.submitMessage()
}

const reloadWindow = () => {
  window.location.reload()
}

// Clean up on component unmount
onBeforeUnmount(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
  if (recorder.value) {
    recorder.value.destroy()
    recorder.value = null
  }
  if (audio.value.url) {
    URL.revokeObjectURL(audio.value.url)
  }
})
</script>

<template>
  <div
    class="alert alert-danger flex h-16 items-center justify-between px-6"
    v-if="!isDeviceSupportAudioInput"
  >
    <p class="">
      {{
        trans('This device does not support voice recording or does not have microphone access.')
      }}
    </p>
    <button type="button" class="btn btn-secondary" @click="reloadWindow">
      {{ trans('Reload') }}
    </button>
  </div>
  <div
    v-else
    class="flex h-16 items-center justify-between rounded-md bg-gray-50 px-4 shadow-sm dark:bg-dark-800"
  >
    <button
      type="button"
      @click="closeRecording"
      class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-gray-200 dark:hover:bg-dark-700"
    >
      <Icon icon="bx:x" class="text-gray-600 dark:text-gray-300" />
    </button>

    <div class="mr-3 flex flex-1 items-center justify-end">
      <div v-if="audio.isRecording" class="flex items-center gap-2">
        <div class="flex items-center gap-1">
          <div class="h-3 w-3 animate-pulse rounded-full bg-red-500"></div>
          <span class="text-sm font-medium text-red-600 dark:text-red-400">{{
            formattedTimer
          }}</span>
        </div>
        <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
          {{ trans('Recording') }}...
        </div>
      </div>

      <div class="rounded-full bg-slate-200 dark:bg-dark-600">
        <audio
          v-if="audio.url && !audio.isRecording"
          :src="audio.url"
          controls
          class="h-10 w-64 opacity-60 dark:opacity-50"
        ></audio>
      </div>
    </div>

    <div class="mr-2 flex items-center gap-3">
      <button
        v-if="audio.url"
        type="button"
        @click="deleteRecord"
        class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-gray-200 dark:hover:bg-dark-700"
      >
        <Icon icon="bx:trash" class="text-red-500 dark:text-red-400" />
      </button>

      <button
        type="button"
        v-if="!audio.url && !audio.isRecording"
        @click="startRecording"
        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 transition-colors hover:bg-gray-300 dark:bg-dark-700 dark:hover:bg-dark-600"
      >
        <Icon icon="bx:microphone" class="text-gray-700 dark:text-gray-200" />
      </button>

      <button
        type="button"
        v-else-if="!audio.url && audio.isRecording"
        @click="stopRecording"
        class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 transition-colors hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50"
      >
        <Icon icon="bx:stop" class="text-xl text-red-600 dark:text-red-400" />
      </button>

      <button
        v-if="audio.url && !audio.isRecording"
        @click="sendMessage"
        :disabled="chatStore.loading.sendingMessage"
        class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 transition-colors hover:bg-green-600"
      >
        <DotLoader v-if="chatStore.loading.sendingMessage" />
        <Icon v-else icon="bx:send" class="text-white" />
      </button>
    </div>
  </div>
</template>
