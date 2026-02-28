<script setup>
import { reactive } from 'vue'
import ImageInput from '@/Components/Forms/ImageInput.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useOptionUpdateStore } from '@/Store/Admin/optionUpdate'
import TextInput from '@/Components/Dashboard/TextInput.vue'

const props = defineProps(['data'])
const formData = reactive({ ...props.data })
const optionUpdate = useOptionUpdateStore()

const addFaqItem = () => {
  formData.block_feature_fourteen.faqs.push({
    question: '',
    answer: ''
  })
}

const removeFaqItem = (index) => {
  formData.block_feature_fourteen.faqs.splice(index, 1)
}

const addFeatureItem = () => {
  formData.block_feature_one.features.push({
    icon: '',
    title: '',
    description: ''
  })
}

const removeFeatureItem = (index) => {
  formData.block_feature_one.features.splice(index, 1)
}

const addLogo = () => {
  formData.app_integration_three.logos.push({
    color: '',
    icon: ''
  })
}

const removeLogo = (index) => {
  formData.app_integration_three.logos.splice(index, 1)
}

const addCounterItem = () => {
  formData.counter_section_one.items.push({
    prefix: '',
    number: '',
    suffix: '',
    subtitle: ''
  })
}

const removeCounterItem = (index) => {
  formData.counter_section_one.items.splice(index, 1)
}
</script>

<template>
  <form
    method="POST"
    @submit.prevent="optionUpdate.submit('about_page', formData)"
    enctype="multipart/form-data"
  >
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Block Feature Fourteen') }}</h6>
      <TextInput
        :label="trans('Title')"
        v-model="formData.block_feature_fourteen.title"
        class="mb-2"
      />
      <div class="mb-2">
        <label class="mr-2">{{ trans('Faqs') }}</label>
        <div
          class="mb-2 flex items-center gap-x-2 rounded border p-2 dark:border-gray-600"
          v-for="(item, index) in formData.block_feature_fourteen.faqs"
          :key="index"
        >
          <span class="rounded-full bg-indigo-600 p-2 py-1 text-center text-white">{{
            index + 1
          }}</span>
          <div class="flex flex-grow flex-col gap-1">
            <TextInput :label="trans('Question')" v-model="item.question" class="mb-2" />
            <TextInput :label="trans('Answer')" v-model="item.answer" class="mb-2" />
          </div>
          <button type="button" @click="removeFaqItem(index)" class="btn btn-danger">X</button>
        </div>
        <button type="button" @click="addFaqItem" class="btn btn-primary">+</button>
      </div>
      <ImageInput
        :label="trans('Image 1')"
        v-model="formData.block_feature_fourteen.image_one"
        class="mb-2"
      />
      <ImageInput
        :label="trans('Image 2')"
        v-model="formData.block_feature_fourteen.image_two"
        class="mb-2"
      />
      <ImageInput
        :label="trans('Image 3')"
        v-model="formData.block_feature_fourteen.image_three"
        class="mb-2"
      />
    </div>
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('About Counter Section') }}</h6>
      <div class="mb-2">
        <label class="mr-2">{{ trans('Counters') }}</label>
        <div
          class="mb-2 flex items-center gap-x-2 rounded border p-2 dark:border-gray-600"
          v-for="(item, index) in formData.counter_section_one.items"
          :key="index"
        >
          <span class="rounded-full bg-indigo-600 p-2 py-1 text-center text-white">{{
            index + 1
          }}</span>
          <div class="flex flex-grow flex-col gap-1">
            <div class="flex gap-1">
              <TextInput :label="trans('Prefix')" v-model="item.prefix" class="mb-2" />
              <TextInput :label="trans('Number')" v-model="item.number" class="mb-2" />
              <TextInput :label="trans('Suffix')" v-model="item.suffix" class="mb-2" />
            </div>
            <TextInput :label="trans('Subtitle')" v-model="item.subtitle" class="mb-2" />
          </div>
          <button type="button" @click="removeCounterItem(index)" class="btn btn-danger">X</button>
        </div>
        <button type="button" @click="addCounterItem" class="btn btn-primary">+</button>
      </div>
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Block Feature One') }}</h6>
      <TextInput :label="trans('Title')" v-model="formData.block_feature_one.title" class="mb-2" />
      <div class="mb-2">
        <label class="mr-2">{{ trans('Features') }}</label>
        <div
          class="mb-2 flex items-center gap-x-2 rounded border p-2 dark:border-gray-600"
          v-for="(item, index) in formData.block_feature_one.features ?? []"
          :key="index"
        >
          <span class="rounded-full bg-indigo-600 p-2 py-1 text-center text-white">{{
            index + 1
          }}</span>
          <div class="flex flex-grow flex-col gap-1">
            <ImageInput :label="trans('Icon')" v-model="item.icon" class="mb-2" />
            <TextInput :label="trans('Title')" v-model="item.title" class="mb-2" />
            <TextInput :label="trans('Description')" v-model="item.description" class="mb-2" />
          </div>
          <button type="button" @click="removeFeatureItem(index)" class="btn btn-danger">X</button>
        </div>
        <button type="button" @click="addFeatureItem" class="btn btn-primary">+</button>
      </div>
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('App Integration Three') }}</h6>
      <TextInput
        :label="trans('Title')"
        v-model="formData.app_integration_three.title"
        class="mb-2"
      />
      <div class="mb-2">
        <label class="mr-2">{{ trans('Items') }}</label>
        <div
          class="mb-2 flex items-center gap-x-2 rounded border p-2 dark:border-gray-600"
          v-for="(item, index) in formData.app_integration_three?.logos ?? []"
          :key="index"
        >
          <span class="rounded-full bg-indigo-600 p-2 py-1 text-center text-white">{{
            index + 1
          }}</span>
          <div class="flex flex-grow items-center gap-1">
            <input type="color" class="color" v-model="item.color" />
            <ImageInput :placeholder="trans('Icon')" v-model="item.icon" class="mb-2" />
          </div>
          <button type="button" @click="removeLogo(index)" class="btn btn-danger">X</button>
        </div>
        <button type="button" @click="addLogo" class="btn btn-primary">+</button>
      </div>

      <TextInput
        :label="trans('Button Text')"
        v-model="formData.app_integration_three.button_text"
        class="mb-2"
      />
      <TextInput
        :label="trans('Button Link')"
        v-model="formData.app_integration_three.button_link"
        class="mb-2"
      />
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Feedback Section One') }}</h6>
      <TextInput
        :label="trans('Title')"
        v-model="formData.feedback_section_one.title"
        class="mb-2"
      />
      <ImageInput
        :label="trans('Image')"
        v-model="formData.feedback_section_one.image"
        class="mb-2"
      />
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Blog Section One') }}</h6>
      <TextInput :label="trans('Title')" v-model="formData.blog_section_one.title" class="mb-2" />
      <TextInput
        :label="trans('Button Text')"
        v-model="formData.blog_section_one.button_text"
        class="mb-2"
      />
      <TextInput
        :label="trans('Blogs IDs (Comma Separated)')"
        v-model="formData.blog_section_one.blog_ids"
        class="mb-2"
      />
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Fancy Banner One') }}</h6>
      <TextInput :label="trans('Title')" v-model="formData.fancy_banner_one.title" class="mb-2" />
      <TextInput
        :label="trans('Button Text')"
        v-model="formData.fancy_banner_one.button_text"
        class="mb-2"
      />
      <TextInput
        :label="trans('Button Link')"
        v-model="formData.fancy_banner_one.button_link"
        class="mb-2"
      />
      <ImageInput
        :label="trans('Shape One')"
        v-model="formData.fancy_banner_one.shape_one"
        class="mb-2"
      />
      <ImageInput
        :label="trans('Shape Two')"
        v-model="formData.fancy_banner_one.shape_two"
        class="mb-2"
      />
    </div>

    <div class="mb-2">
      <SpinnerBtn :processing="optionUpdate.processing" :btn-text="trans('Save Changes')" />
    </div>
  </form>
</template>
