<script setup>
import { defineAsyncComponent, computed, ref } from 'vue'
import UnsupportedMessage from '@whatsapp/Components/Preview/UnsupportedMessage.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import trans from '@/Composables/transComposable'
import toastr from '@/Composables/toastComposable'

const props = defineProps({
  message: {
    type: Object,
    required: true
  }
})

const dynamicMessagePreview = () => {
  const messageType = props.message.type
  const componentName = `${messageType[0].toUpperCase()}${messageType.slice(1)}Message`
  return defineAsyncComponent({
    loader: () => import(`@whatsapp/Components/Preview/${componentName}.vue`),
    errorComponent: UnsupportedMessage
    // hydrate: hydrateOnVisible()
  })
}

const isLoading = ref(false)

const needsRefresh = computed(() => {
  return props.message.body?.mime_type && props.message.meta?.media_url == ''
})

const loadAttachment = () => {
  if (isLoading.value) return
  isLoading.value = true
  axios
    .post(route('user.whatsapp.messages.load-attachment', props.message.uuid))
    .then((response) => {
      console.log('Attachment loaded:', response.data)
      props.message.meta = response.data
      needsRefresh.value = false
    })
    .catch((error) => {
      toastr.danger(error.response.data?.message ?? 'Server Error')
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<template>
  <div
    :title="message.type"
    class="text-danger rounded-primary rounded-tl-none bg-slate-100 p-2 text-sm group-[.pr]:rounded-tl-primary group-[.pr]:rounded-tr-none group-[.pr]:text-white dark:bg-slate-700 dark:text-slate-300"
  >
    <div
      v-if="needsRefresh"
      class="dark: m-2 flex justify-center rounded-lg border border-gray-700 bg-gray-100 p-5 dark:bg-gray-800"
    >
      <SpinnerBtn
        v-if="needsRefresh"
        @click="loadAttachment"
        :processing="isLoading"
        :btn-text="isLoading ? 'Loading...' : trans('Refresh')"
        icon="bx:refresh"
        title="Refresh Message Attachment"
        classes="flex justify-center items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
      >
      </SpinnerBtn>
    </div>
    <component :is="dynamicMessagePreview()" :message="message" />
  </div>
</template>
