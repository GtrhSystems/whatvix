<script setup>
import { reactive } from 'vue'

import ImageInput from '@/Components/Forms/ImageInput.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useOptionUpdateStore } from '@/Store/Admin/optionUpdate'
import TextInput from '@/Components/Dashboard/TextInput.vue'

const props = defineProps(['data'])
const formData = reactive({ ...props.data })
const optionUpdate = useOptionUpdateStore()
</script>

<template>
  <form
    method="POST"
    @submit.prevent="optionUpdate.submit('contact_page', formData)"
    enctype="multipart/form-data"
  >
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <TextInput v-model="formData.map_location" :label="trans('Map Location')" />
      <TextInput v-model="formData.title" :label="trans('Title')" />

      <h6>{{ trans('Features') }}</h6>
      <div class="flex gap-2">
        <ImageInput
          :label="trans('Feature One Icon')"
          v-model="formData.feature_one_icon"
          class="mb-2"
        />
        <TextInput v-model="formData.feature_one_title" :label="trans('Feature One Title')" />
        <TextInput
          v-model="formData.feature_one_description"
          :label="trans('Feature One Description')"
        />
      </div>

      <div class="flex gap-2">
        <ImageInput
          :label="trans('Feature Two Icon')"
          v-model="formData.feature_two_icon"
          class="mb-2"
        />
        <TextInput v-model="formData.feature_two_title" :label="trans('Feature Two Title')" />
        <TextInput
          v-model="formData.feature_two_description"
          :label="trans('Feature Two Description')"
        />
      </div>

      <div class="flex gap-2">
        <ImageInput
          :label="trans('Feature Three Icon')"
          v-model="formData.feature_three_icon"
          class="mb-2"
        />
        <TextInput v-model="formData.feature_three_title" :label="trans('Feature Three Title')" />
        <TextInput
          v-model="formData.feature_three_description"
          :label="trans('Feature Three Description')"
        />
      </div>
      <TextInput v-model="formData.form_title" :label="trans('Form Title')" />
      <TextInput v-model="formData.form_button_text" :label="trans('Form Button Text')" />
    </div>

    <div class="mb-2">
      <SpinnerBtn :processing="optionUpdate.processing" :btn-text="trans('Save Changes')" />
    </div>
  </form>
</template>
