<script setup>
import { ref } from 'vue'

import RichEditor from '@/Components/Forms/RichEditor.vue'
import InputFieldError from '@/Components/InputFieldError.vue'
import sharedComposable from '@/Composables/sharedComposable'
import toast from '@/Composables/toastComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

defineOptions({ layout: UserLayout })
const { copyToClipboard } = sharedComposable()
const props = defineProps(['aiGenerated'])

const form = useForm({
  ...props.aiGenerated
})
const documentName = ref('')
const submit = () => {
  form.patch(route('user.ai-generated-history.update', props.aiGenerated.uuid), {
    onSuccess: () => {
      form.reset()
      toast.success('Submitted Successfully')
    }
  })
}
function downloadHTMLFile(textContent, name) {
  const blob = new Blob([textContent], { type: 'text/html' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = name ? name.replace(' ', '-') : 'untitled.html'

  a.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <form @submit.prevent="submit" class="card card-body mx-auto mt-5 w-11/12 space-y-4 lg:w-8/12">
    <p class="text-xl">{{ trans('Template') }}</p>

    <div class="flex">
      <button class="me-1" type="button" @click="copyToClipboard(form.content)">
        <i class="bx bx-file text-2xl"></i>
      </button>
      <button class="ms-1" type="button" @click="downloadHTMLFile(form.content, documentName)">
        <i class="bx bx-download text-2xl"></i>
      </button>
    </div>
    <div>
      <input
        v-model="documentName"
        type="text"
        class="input w-full"
        placeholder="Untitled Document..."
      />
    </div>
    <div class="ai-ck-section">
      <RichEditor v-model="form.content" />
      <InputFieldError class="mb-5" :message="form.errors.content" />
    </div>
    <div>
      <SpinnerBtn :processing="form.processing" classes="btn btn-primary" btn-text="Update" />
    </div>
  </form>
</template>
