<script setup>
import { reactive } from 'vue'

import ImageInput from '@/Components/Forms/ImageInput.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useOptionUpdateStore } from '@/Store/Admin/optionUpdate'
import TextInput from '@/Components/Dashboard/TextInput.vue'
import trans from '@/Composables/transComposable'

const props = defineProps(['data'])
const formData = reactive({ ...props.data })
const optionUpdate = useOptionUpdateStore()

if (!formData.socials) {
  formData.socials = {}
}
</script>

<template>
  <form
    method="POST"
    @submit.prevent="optionUpdate.submit('primary_data', formData)"
    enctype="multipart/form-data"
  >
    <h6>{{ trans('Primary Settings') }}</h6>
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <ImageInput :label="trans('Site Logo (Light)')" v-model="formData.logo" class="mb-2" />
      <ImageInput :label="trans('Site Logo (Dark)')" v-model="formData.deep_logo" class="mb-2" />
      <ImageInput :label="trans('Favicon')" v-model="formData.favicon" class="mb-2" />
      <div class="mb-2">
        <label>{{ trans('Email address') }}</label>
        <input type="email" v-model="formData.contact_email" class="input" />
      </div>
      <div class="mb-2">
        <label>{{ trans('Contact Phone') }}</label>
        <input type="number" class="input" v-model="formData.contact_phone" />
      </div>

      <div class="mb-2">
        <label>{{ trans('Contact Address') }}</label>
        <input type="text" class="input" v-model="formData.contact_address" />
      </div>

      <div class="mb-2">
        <label>{{ trans('Footer Text') }}</label>
        <input type="text" class="input" v-model="formData.footer_text" />
      </div>

      <div class="mb-2">
        <label>{{ trans('Footer Copyright text') }}</label>
        <input type="text" class="input" v-model="formData.copyright_text" />
      </div>
    </div>

    <h6>{{ trans('Social Networks') }}</h6>
    <div class="mb-10 mt-2 rounded border p-3 dark:border-gray-600">
      <div class="mb-2">
        <label>{{ trans('Facebook Profile Link') }}</label>
        <input
          type="url"
          name="socials[facebook]"
          class="input"
          v-model="formData.socials.facebook"
        />
      </div>
      <div class="mb-2">
        <label>{{ trans('Youtube Profile Link') }}</label>
        <input
          type="url"
          name="socials[youtube]"
          class="input"
          v-model="formData.socials.youtube"
        />
      </div>
      <div class="mb-2">
        <label>{{ trans('Twitter Profile Link') }}</label>
        <input
          type="url"
          name="socials[twitter]"
          class="input"
          v-model="formData.socials.twitter"
        />
      </div>
      <div class="mb-2">
        <label>{{ trans('Instagram Profile Link') }}</label>
        <input
          type="url"
          name="socials[instagram]"
          class="input"
          v-model="formData.socials.instagram"
        />
      </div>
      <div class="mb-2">
        <label>{{ trans('Linkedin Profile Link') }}</label>
        <input
          type="url"
          name="socials[linkedin]"
          class="input"
          v-model="formData.socials.linkedin"
        />
      </div>
    </div>

    <h6>{{ trans('Fancy Banner Six') }}</h6>
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <ImageInput v-model="formData.fancy_banner_six.title_image" label="Title Image" />
      <TextInput v-model="formData.fancy_banner_six.title" :label="trans('Title')" />
      <small>{{ trans('set title image position by') }} {image}</small>
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
