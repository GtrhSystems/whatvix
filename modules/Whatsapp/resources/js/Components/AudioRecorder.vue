<template>
  <div
    class="flex h-full items-center justify-end gap-2 rounded-md bg-gray-50 px-2 dark:bg-dark-800"
  >
    <div
      class="flex gap-2 text-center"
      :class="audio.isRecording ? 'w-40' : 'w-80'"
      v-if="(audio.url && !audio.isRecording) || audio.isRecording"
    >
      <button type="button" @click="close">
        <Icon icon="bx:trash" class="text-xl text-red-600" />
      </button>
      <div v-if="audio.isRecording" class="font-semibold text-green-600">
        {{ trans('Recording...') }}
      </div>
      <audio v-if="audio.url && !audio.isRecording" :src="audio.url" controls></audio>
    </div>

    <div class="flex items-center gap-2">
      <button type="button" v-if="!audio.isRecording" @click="startRecording">
        <Icon icon="bx:microphone" class="text-xl" />
      </button>
      <button type="button" v-else @click="stopRecording">
        <Icon icon="bx:microphone" class="text-xl text-red-600" />
      </button>
      <button @click="sendMessage">
        <template v-if="!isLoading">
          <Icon icon="bx:send" class="text-xl" />
        </template>
        <DotLoader v-else />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
const props = defineProps({
  isLoading: {
    type: Boolean,
    default: false
  }
})
watch(
  () => props.isLoading,
  function (newValue, OldValue) {
    if (newValue) {
      isLoading.value = true
    }
  }
)

const audio = ref({
  url: null,
  file: null,
  isRecording: false
})
const mediaRecorder = ref(null)
const recordedChunks = ref([])
const visualizer = ref(null)
const audioStream = ref(null)
const startRecording = () => {
  navigator.mediaDevices
    .getUserMedia({ audio: true })
    .then((stream) => {
      audioStream.value = stream
      mediaRecorder.value = new MediaRecorder(stream)

      mediaRecorder.value.ondataavailable = (event) => {
        if (event.data.size > 0) {
          recordedChunks.value.push(event.data)
        }
      }

      mediaRecorder.value.onstop = () => {
        const audioBlob = new Blob(recordedChunks.value, {
          type: 'audio/mp3'
        })
        audio.value.url = URL.createObjectURL(audioBlob)
        audio.value.file = audioBlob
      }

      mediaRecorder.value.start()
      audio.value.isRecording = true
    })
    .catch((error) => {
      console.error('Error accessing microphone:', error)
    })
}
const stopRecording = () => {
  if (mediaRecorder.value) {
    mediaRecorder.value.stop()

    if (audioStream.value) {
      audioStream.value.getTracks().forEach((track) => {
        track.stop()
      })
    }

    audioStream.value = null
    audio.value.isRecording = false
  }
}

const emit = defineEmits({
  closeAudioStream: () => {},
  sendMessage: (message) => {}
})

const close = () => {
  stopRecording()
  emit('closeAudioStream')
}
const isLoading = ref(false)
const sendMessage = () => {
  isLoading.value = true
  stopRecording()
  setTimeout(() => {
    emit('sendMessage', audio.value.file)
  }, 1000)
}
</script>
