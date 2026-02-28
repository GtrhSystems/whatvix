<script setup>
import { onMounted } from 'vue'

import RichEditor from '@/Components/Forms/RichEditor.vue'
import InputFieldError from '@/Components/InputFieldError.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useAiToolsStore } from '@/Store/aiToolsStore'
import Multiselect from '@vueform/multiselect'

defineOptions({ layout: UserLayout })

const props = defineProps(['template', 'languages', 'credits'])
const { copyToClipboard, trim, uiAvatar } = sharedComposable()
const store = useAiToolsStore()

onMounted(() => {
  store.$patch({
    props: props,
    form: {
      model: props.template.ai_model,
      template_id: props.template.id,
      fields: props.template.fields
    }
  })
})
</script>

<template>
  <div class="grid grid-cols-1 place-items-start gap-5 lg:grid-cols-12">
    <form @submit.prevent="store.submit" class="card card-body col-span-1 space-y-6 lg:col-span-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <img
            :src="uiAvatar(template.title, template.icon)"
            width="50"
            height="50"
            class="rounded-full"
            alt="icon"
          />
          <p class="mx-2 text-sm">{{ template.title }}</p>
        </div>
        <p class="capitalize text-gray-500">{{ trim(template.prompt_type) }}</p>
      </div>
      <!-- credits -->

      <!-- text -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label mb-1">{{ trans('Language') }}*</label>
          <Multiselect
            class="multiselect-dark"
            v-model="store.form.language"
            :options="languages"
            valueProp="name"
            label="name"
            placeholder="Language"
          />
          <InputFieldError v-if="store.errors?.language" :message="store.errors?.language[0]" />
        </div>
        <div>
          <label class="label mb-1">{{ trans('Tone') }}*</label>
          <Multiselect
            class="multiselect-dark"
            v-model="store.form.tone"
            :options="store.tones"
            placeholder="Tone"
          />
          <InputFieldError v-if="store.errors?.tone" :message="store.errors?.tone[0]" />
        </div>
      </div>

      <!-- fields -->
      <div>
        <div v-for="(field, i) in store.form.fields" :key="i">
          <label class="label mb-1">{{ field.name }}*</label>
          <input
            required
            v-if="field.type === 'input'"
            v-model="field.value"
            type="text"
            class="input"
            :placeholder="field.placeholder"
          />
          <textarea
            v-if="field.type === 'textarea'"
            required
            v-model="field.value"
            class="textarea"
            :placeholder="field.placeholder"
          ></textarea>
          <InputFieldError v-if="store.hasError" :message="store.fieldErrors[field.name]" />
          <InputFieldError
            v-if="!store.hasError && store.errors && store.errors['fields.' + i + '.value']"
            :message="store.errors['fields.' + i + '.value'][0]"
          />
        </div>
      </div>

      <div>
        <label class="label">{{ trans('Maximum Word Limit') }}</label>
        <input
          v-model.number="store.form.max_token"
          type="text"
          class="input"
          placeholder="Maximum Length"
        />
        <InputFieldError v-if="store.errors?.max_token" :message="store.errors?.max_token[0]" />
      </div>
      <div class="flex items-center gap-5">
        <div class="flex-1">
          <label class="label mb-1">{{ trans('Creativity Level') }}</label>
          <Multiselect
            class="multiselect-dark"
            v-model="store.form.creativity"
            :options="store.creativityLevels"
            value-by="value"
            label="label"
            placeholder="Creativity Level"
          />
          <InputFieldError v-if="store.errors?.creativity" :message="store.errors.creativity[0]" />
        </div>
      </div>

      <SpinnerBtn
        classes="btn btn-primary w-full py-3"
        :processing="store.isProcessing"
        :btn-text="trans('Generate')"
      />
    </form>
    <div class="card col-span-1 flex flex-col p-3 lg:col-span-7">
      <div class="user-activity-chart h-100 rounded p-4">
        <div class="flex">
          <button class="me-1" @click="copyToClipboard(store.generatedText)">
            <i class="bx bx-clipboard text-2xl"></i>
          </button>
          <button
            class="ms-1"
            @click="store.downloadHTMLFile(store.generatedText, store.documentName)"
          >
            <i class="bx bx-download text-2xl"></i>
          </button>
        </div>

        <div class="my-8">
          <input
            v-model="store.documentName"
            type="text"
            class="input"
            placeholder="Untitled Document..."
          />
        </div>
        <div class="ai-ck-section">
          <RichEditor v-model="store.generatedText" />
        </div>
      </div>
    </div>
  </div>
</template>
