<script setup>
import { reactive } from 'vue'
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
    @submit.prevent="optionUpdate.submit('blog_page', formData)"
    enctype="multipart/form-data"
  >
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Sidebar Card') }}</h6>
      <TextInput :label="trans('Title')" v-model="formData.card.title" class="mb-2" />
      <TextInput :label="trans('Button Text')" v-model="formData.card.button_text" class="mb-2" />
      <TextInput :label="trans('Button Link')" v-model="formData.card.button_link" class="mb-2" />
    </div>

    <div class="mb-2">
      <SpinnerBtn :processing="optionUpdate.processing" :btn-text="trans('Save Changes')" />
    </div>
  </form>
</template>
