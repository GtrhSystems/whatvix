<script setup>
import ImageInput from '@/Components/Forms/ImageInput.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useOptionUpdateStore } from '@/Store/Admin/optionUpdate'
import TextInput from '@/Components/Dashboard/TextInput.vue'
import { reactive } from 'vue'
const props = defineProps(['data'])
const formData = reactive({ ...(props.data ?? {}) })
const optionUpdate = useOptionUpdateStore()
</script>

<template>
  <form @submit.prevent="optionUpdate.submit('home_page', formData)">
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Hero Banner') }}</h6>
      <TextInput v-model="formData.hero_banner.title" :label="trans('Title')" />
      <TextInput v-model="formData.hero_banner.subtitle" :label="trans('Sub title')" />
      <TextInput v-model="formData.hero_banner.btn_text" :label="trans('Button Text')" />
      <TextInput v-model="formData.hero_banner.feature_item_one" :label="trans('Feature Item 1')" />
      <TextInput v-model="formData.hero_banner.feature_item_two" :label="trans('Feature Item 2')" />
      <ImageInput v-model="formData.hero_banner.bg_image" label="Bg Image" />
      <ImageInput v-model="formData.hero_banner.bg_img_overflow" label="Overflow Image" />
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Feature Block Seven') }}</h6>
      <div
        class="flex items-center gap-2"
        v-for="(item, index) in formData.block_feature_seven?.feature_list || []"
        :key="item"
      >
        <input type="color" class="rounded" v-model="item.color" />
        <TextInput
          v-model="item.title"
          :placeholder="`Feature ${index + 1} Title`"
          class="w-full"
        />
      </div>
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Feature Block Eight') }}</h6>
      <TextInput v-model="formData.block_feature_eight.title" :label="trans('Title')" />
      <TextInput v-model="formData.block_feature_eight.subtitle" :label="trans('Sub title')" />
      <ImageInput v-model="formData.block_feature_eight.bg_image" label="Bg Image" />
      <ImageInput v-model="formData.block_feature_eight.shape_left" label="Shape Left" />
      <ImageInput v-model="formData.block_feature_eight.shape_right" label="Shape Right" />
      <ImageInput v-model="formData.block_feature_eight.card_top" label="Card Top Image" />
      <ImageInput v-model="formData.block_feature_eight.card_bottom" label="Card Bottom Image" />
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Feature Block Nine') }}</h6>
      <!-- card One -->
      <p class="mb-2 mt-4 font-bold">{{ trans('Card One') }}</p>
      <ImageInput v-model="formData.block_feature_nine.card_one_left_image" label="Left Image" />
      <TextInput
        v-model="formData.block_feature_nine.card_one_top_title"
        :label="trans('Top Title')"
      />
      <TextInput v-model="formData.block_feature_nine.card_one_title" :label="trans('Title')" />
      <TextInput
        v-model="formData.block_feature_nine.card_one_subtitle"
        :label="trans('Sub title')"
      />

      <!-- card Two -->
      <p class="mb-2 mt-4 font-bold">{{ trans('Card Two') }}</p>
      <ImageInput v-model="formData.block_feature_nine.card_two_left_image" label="Left Image" />
      <TextInput
        v-model="formData.block_feature_nine.card_two_top_title"
        :label="trans('Top Title')"
      />
      <TextInput v-model="formData.block_feature_nine.card_two_title" :label="trans('Title')" />
      <TextInput
        v-model="formData.block_feature_nine.card_two_subtitle"
        :label="trans('Sub title')"
      />

      <!-- card Three -->
      <p class="mb-2 mt-4 font-bold">{{ trans('Card Three') }}</p>
      <ImageInput v-model="formData.block_feature_nine.card_three_left_image" label="Left Image" />
      <TextInput
        v-model="formData.block_feature_nine.card_three_top_title"
        :label="trans('Top Title')"
      />
      <TextInput v-model="formData.block_feature_nine.card_three_title" :label="trans('Title')" />
      <TextInput
        v-model="formData.block_feature_nine.card_three_subtitle"
        :label="trans('Sub title')"
      />
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Application Integration Two') }}</h6>
      <TextInput v-model="formData.application_integration_two.title" :label="trans('Title')" />
      <TextInput
        v-model="formData.application_integration_two.subtitle"
        :label="trans('Sub title')"
      />

      <p class="mb-2 mt-4 font-bold">{{ trans('Left Side') }}</p>
      <ImageInput
        v-model="formData.application_integration_two.left_image_one"
        label="Left Image One"
      />
      <ImageInput
        v-model="formData.application_integration_two.left_image_two"
        label="Left Image Two"
      />
      <ImageInput
        v-model="formData.application_integration_two.left_image_three"
        label="Left Image Three"
      />
      <ImageInput
        v-model="formData.application_integration_two.left_image_four"
        label="Left Image Four"
      />

      <p class="mb-2 mt-4 font-bold">{{ trans('Center') }}</p>
      <ImageInput
        v-model="formData.application_integration_two.center_image"
        label="Center Image"
      />
      <TextInput
        v-model="formData.application_integration_two.center_title"
        :label="trans('Center Title')"
      />
      <TextInput
        v-model="formData.application_integration_two.center_subtitle"
        :label="trans('Center Sub Title')"
      />

      <p class="mb-2 mt-4 font-bold">{{ trans('Right Side') }}</p>
      <ImageInput
        v-model="formData.application_integration_two.right_image_one"
        label="Right Image One"
      />
      <ImageInput
        v-model="formData.application_integration_two.right_image_two"
        label="Right Image Two"
      />
      <ImageInput
        v-model="formData.application_integration_two.right_image_three"
        label="Right Image Three"
      />
      <ImageInput
        v-model="formData.application_integration_two.right_image_four"
        label="Right Image Four"
      />
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Feedback Section Four') }}</h6>
      <TextInput v-model="formData.feedback_section_four.counter" :label="trans('Counter')" />
      <ImageInput v-model="formData.feedback_section_four.image" label="Image" />
      <TextInput v-model="formData.feedback_section_four.title" :label="trans('Title')" />
      <TextInput v-model="formData.feedback_section_four.subtitle" :label="trans('Sub title')" />
    </div>

    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Faq Section One') }}</h6>
      <TextInput v-model="formData.faq_section_one.title" :label="trans('Title')" />
    </div>
    <div class="mb-10 mt-2 space-y-2 rounded border p-3 dark:border-gray-600">
      <h6>{{ trans('Fancy Banner Three') }}</h6>
      <div>
        <TextInput v-model="formData.fancy_banner_three.title" :label="trans('Title')" />
        <p class="mt-1 text-sm text-gray-400">{{ trans('wrap with {text} to decorate') }}</p>
      </div>
      <TextInput v-model="formData.fancy_banner_three.subtitle" :label="trans('Sub title')" />
      <TextInput v-model="formData.fancy_banner_three.button_text" :label="trans('Button Text')" />
      <TextInput v-model="formData.fancy_banner_three.button_link" :label="trans('Button Link')" />
    </div>

    <div class="my-2">
      <SpinnerBtn :processing="optionUpdate.processing" :btn-text="trans('Save Changes')" />
    </div>
  </form>
</template>
