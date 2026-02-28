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
    @submit.prevent="optionUpdate.submit('pricing_page', formData)"
    enctype="multipart/form-data"
  >
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <TextInput v-model="formData.title" :label="trans('Title')" />
    </div>
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
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
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Featured Plans') }}</h6>
      <TextInput v-model="formData.featured_plan_title" :label="trans('Title')" />
    </div>

    <div class="mb-2">
      <SpinnerBtn :processing="optionUpdate.processing" :btn-text="trans('Save Changes')" />
    </div>
  </form>
</template>
