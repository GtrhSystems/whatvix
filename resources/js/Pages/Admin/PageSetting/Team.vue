<script setup>
import { onBeforeMount, reactive, ref } from 'vue'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useOptionUpdateStore } from '@/Store/Admin/optionUpdate'
import TextInput from '@/Components/Dashboard/TextInput.vue'
import ImageInput from '@/Components/Forms/ImageInput.vue'

const props = defineProps(['data'])
const formData = reactive({ ...props.data })
const optionUpdate = useOptionUpdateStore()

onBeforeMount(() => {
  let properties = ['list', 'fancy_banner_six']
  properties.forEach((key) => (formData[key] = formData[key] || {}))
})
</script>

<template>
  <form
    method="POST"
    @submit.prevent="optionUpdate.submit('team_page', formData)"
    enctype="multipart/form-data"
  >
    <h6>{{ trans('Team List') }}</h6>
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <TextInput v-model="formData.list.title" :label="trans('Title')" />
      <TextInput v-model="formData.list.subtitle" :label="trans('Sub title')" />
    </div>

    <h6>{{ trans('Fancy Banner Six') }}</h6>
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <ImageInput v-model="formData.fancy_banner_six.title_img" label="Title Image" />
      <TextInput v-model="formData.fancy_banner_six.title" :label="trans('Title')" />
      <small>set title image position by {image}</small>
      <TextInput v-model="formData.fancy_banner_six.subtitle" :label="trans('Sub title')" />
      <ImageInput v-model="formData.fancy_banner_six.left_image" label="Left Image" />
      <ImageInput v-model="formData.fancy_banner_six.right_image" label="Right Image" />
      <TextInput v-model="formData.fancy_banner_six.button_text" :label="trans('Button Text')" />
      <TextInput v-model="formData.fancy_banner_six.button_link" :label="trans('Button Link')" />
    </div>

    <div class="mb-2">
      <SpinnerBtn :processing="optionUpdate.processing" :btn-text="trans('Save Changes')" />
    </div>
  </form>
</template>
