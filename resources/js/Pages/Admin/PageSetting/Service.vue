<script setup>
import { reactive } from 'vue'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useOptionUpdateStore } from '@/Store/Admin/optionUpdate'
import TextInput from '@/Components/Dashboard/TextInput.vue'
import ImageInput from '@/Components/Forms/ImageInput.vue'

const props = defineProps(['data'])
const formData = reactive({ ...props.data })
const optionUpdate = useOptionUpdateStore()

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
    @submit.prevent="optionUpdate.submit('service_page', formData)"
    enctype="multipart/form-data"
  >
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Service List') }}</h6>
      <TextInput v-model="formData.list.title" :label="trans('Title')" />
      <TextInput v-model="formData.list.subtitle" :label="trans('Sub Title')" />
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Block Feature 25') }}</h6>
      <TextInput
        v-model="formData.block_feature_twenty_five.top_title"
        :label="trans('Top Title')"
      />
      <TextInput v-model="formData.block_feature_twenty_five.title" :label="trans('Title')" />
      <TextInput
        v-model="formData.block_feature_twenty_five.subtitle"
        :label="trans('Sub Title')"
      />
      <ImageInput v-model="formData.block_feature_twenty_five.image" :label="trans('Image')" />
      <div class="mt-2 flex gap-1">
        <TextInput
          v-model="formData.block_feature_twenty_five.counter.prefix"
          :label="trans('Counter Prefix')"
        />
        <TextInput
          v-model="formData.block_feature_twenty_five.counter.number"
          :label="trans('Number')"
        />
        <TextInput
          v-model="formData.block_feature_twenty_five.counter.suffix"
          :label="trans('Suffix')"
        />
        <TextInput
          v-model="formData.block_feature_twenty_five.counter.subtitle"
          :label="trans('Subtitle')"
        />
      </div>
      <div class="mb-2">
        <label class="mr-2">{{ trans('Features') }}</label>
        <div
          class="mb-2 flex items-center gap-x-2 rounded border p-2 dark:border-gray-600"
          v-for="(feature, index) in formData.block_feature_twenty_five.features"
          :key="index"
        >
          <span class="rounded-full bg-indigo-600 p-2 py-1 text-center text-white">
            {{ index + 1 }}
          </span>
          <div class="flex flex-grow flex-col gap-1">
            <ImageInput :label="trans('Icon')" v-model="feature.icon" class="mb-2" />
            <TextInput :label="trans('Title')" v-model="feature.title" class="mb-2" />
          </div>
        </div>
      </div>
      <div class="mb-2">
        <label class="mr-2">{{ trans('Counters') }}</label>
        <div
          class="mb-2 flex items-center gap-x-2 rounded border p-2 dark:border-gray-600"
          v-for="(counter, index) in formData.block_feature_twenty_five.counters"
          :key="index"
        >
          <span class="rounded-full bg-indigo-600 p-2 py-1 text-center text-white">
            {{ index + 1 }}
          </span>
          <div class="flex gap-2">
            <TextInput :label="trans('Prefix')" v-model="counter.prefix" />
            <TextInput :label="trans('Number')" v-model="counter.number" />
            <TextInput :label="trans('Suffix')" v-model="counter.suffix" />
            <TextInput :label="trans('Subtitle')" v-model="counter.subtitle" />
          </div>
        </div>
      </div>
    </div>

    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Counter Section One') }}</h6>
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
