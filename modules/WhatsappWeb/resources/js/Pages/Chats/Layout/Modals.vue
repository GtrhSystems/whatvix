<script setup>
import { computed, ref } from 'vue'

import axios from 'axios'
import { storeToRefs } from 'pinia'

import { useForm } from '@inertiajs/vue3'
import AssetModal from '@/Components/Dashboard/AssetModal.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import RichEditor from '@/Components/Forms/RichEditor.vue'
import InputFieldError from '@/Components/InputFieldError.vue'
import Multiselect from '@/Components/Forms/MultiSelect.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useAssetStore } from '@/Store/assetStore'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import { useModalStore } from '@/Store/modalStore'
import sharedComposable from '@/Composables/sharedComposable'
import TemplateMessageModal from '@whatsappWeb/Pages/Chats/Modals/TemplateMessageModal.vue'
import { useAiToolsStore } from '@/Store/aiToolsStore'
import QuickReplyModal from '@whatsappWeb/Pages/Chats/Modals/QuickReplyModal.vue'

const chatStore = useChatStore()
const assetStore = useAssetStore()
const { uiAvatar, textExcerpt, trim } = sharedComposable()
const modalStore = useModalStore()
const aiStore = useAiToolsStore()

const selectedAiTemplate = ref(null)
const setAiTemplate = (template) => {
  selectedAiTemplate.value = template
  const findTemplate = chatStore.aiTemplates.find((t) => t.id == template.id)
  aiStore.$patch({
    props: { template: findTemplate },
    form: {
      model: template.ai_model,
      template_id: template.id,
      fields: template.fields
    }
  })
}
const bookmark = (id, bookmarked) => {
  router.post(
    route('user.ai-tools.bookmark'),
    {
      ai_template_id: id
    },
    {
      onSuccess: () => {
        if (bookmarked == 1) {
          toast.danger('Bookmarked Removed Successfully')
        } else {
          toast.success('Bookmarked Successfully')
        }
      }
    }
  )
}

// ------------> badge start <-------------
const assignBadgeForm = useForm({
  module: 'whatsapp-web',
  badge_id: ''
})

const assignBadgeToChat = () => {
  assignBadgeForm.put(route('user.conversations.badge.assign', chatStore.activeConversation.id), {
    onSuccess: () => {
      modalStore.close('addBadgeModal')
      chatStore.activeConversation.badge_id = assignBadgeForm.badge_id
      assignBadgeForm.reset()
    }
  })
}

const badgeForm = useForm({
  text: '',
  color: ''
})

const addNewBadge = () => {
  badgeForm.post(route('user.badges.store'), {
    onSuccess: () => {
      modalStore.close('badgeModal')
      badgeForm.reset()
      chatStore.loadBadges()
    }
  })
}
// ------------> badge end <-------------

const { inputMessage } = storeToRefs(chatStore)

const sendMediaMessage = () => {
  inputMessage.value.file = assetStore.getFormData('file')

  if (!inputMessage.value.file || !inputMessage.value.type) {
    return toast.warning('Invalid media data')
  }
  chatStore.submitMessage()
}

const locationForm = useForm({
  latitude: '',
  longitude: ''
})

const sendLocationMessage = () => {
  if (!locationForm.latitude || !locationForm.longitude) {
    return toast.warning('Invalid location data')
  }

  inputMessage.value.type = 'location'
  inputMessage.value.message = {
    latitude: locationForm.latitude,
    longitude: locationForm.longitude
  }

  chatStore.submitMessage()
}
</script>

<template>
  <!-- global modals -->
  <QuickReplyModal />

  <!-- Template Message Modal -->
  <TemplateMessageModal />

  <!-- Add Badge Modal start -->
  <Modal :header-state="true" header-title="Add New Badge" state="badgeModal">
    <form @submit.prevent="addNewBadge">
      <div class="mb-3 flex items-center justify-between gap-2">
        <input type="text" placeholder="Badge Text" v-model="badgeForm.text" class="input" />
        <input type="color" v-model="badgeForm.color" class="" />
      </div>

      <SpinnerBtn :processing="badgeForm.processing" :btn-text="trans('Save')" class="w-full" />
    </form>
  </Modal>
  <!-- Add Badge Modal end -->

  <!-- Add Conversation Badge Modal start -->
  <Modal :header-state="true" header-title="Assign Badge" state="addBadgeModal">
    <form @submit.prevent="assignBadgeToChat">
      <div class="mb-3 flex items-center justify-between gap-2">
        <select class="input" v-model="assignBadgeForm.badge_id">
          <option value="">{{ trans('Select Badge') }}</option>
          <option v-for="badge in chatStore.badges" :value="badge.id" :key="badge.id">
            <span :style="{ color: badge.color }">{{ badge.text }}</span>
          </option>
        </select>

        <SpinnerBtn :processing="assignBadgeForm.processing" :btn-text="trans('Save')" />
      </div>
    </form>
  </Modal>
  <!-- Add Conversation Badge Modal end -->

  <!-- AI Template Modal start -->
  <Modal
    @close="selectedAiTemplate = null"
    modal-type="sidebar"
    :modal-size="selectedAiTemplate ? 'w-4/12' : 'w-3/12'"
    :header-state="true"
    header-title="AI Tools"
    state="aiModal"
  >
    <div v-if="!selectedAiTemplate" class="mt-4 grid grid-cols-1 gap-4">
      <template v-for="temp in chatStore.aiTemplates" :key="temp.id">
        <div class="card card-body">
          <div class="flex items-center justify-start gap-4 border-b border-primary-600 pb-4">
            <button type="button" @click="setAiTemplate(temp)" class="logo h-10 w-10">
              <img
                v-lazy="uiAvatar(temp.title, temp.icon)"
                alt="image"
                class="h-full w-full rounded-full"
              />
            </button>
            <button type="button" class="flex flex-col items-start" @click="setAiTemplate(temp)">
              <p class="ml-0 text-xs font-semibold capitalize">
                {{ textExcerpt(temp.title, 65) }}
              </p>
              <small class="text-xs capitalize text-gray-500">{{ trim(temp.prompt_type) }}</small>
            </button>

            <button
              @click="bookmark(temp.id, temp.isBookmarked)"
              type="button"
              class="btn ml-auto h-8 w-8 rounded-full"
              :class="{
                'btn-primary': temp.isBookmarked == 1,
                'btn-outline-primary': temp.isBookmarked == 0
              }"
            >
              <i class="bx bx-bookmark-alt-minus text-lg"></i>
            </button>
          </div>
          <div v-html="textExcerpt(temp.description, 100)" class="pt-3 text-sm"></div>
        </div>
      </template>
    </div>
    <!-- form -->
    <div v-else class="mt-4 px-1">
      <form @submit.prevent="aiStore.submit" class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <img
              :src="uiAvatar(selectedAiTemplate.title, selectedAiTemplate.icon)"
              class="h-10 w-10 rounded-full"
              alt="icon"
            />
            <div class="mx-2">
              <p class="text-sm leading-3">{{ selectedAiTemplate.title }}</p>
              <small class="text-xs capitalize text-gray-500">
                {{ trim(selectedAiTemplate.prompt_type) }}
              </small>
            </div>
          </div>

          <button
            class="btn btn-outline-danger btn-xs"
            @click="
              () => {
                selectedAiTemplate = null
                aiStore.form.reset()
                aiStore.generatedText = ''
              }
            "
          >
            <span>{{ trans('Reset') }}</span>
            <Icon icon="bx:refresh" class="text-lg" />
          </button>
        </div>
        <!-- credits -->

        <!-- text -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label mb-1">{{ trans('Language') }}*</label>
            <Multiselect
              mode="single"
              class="multiselect-dark"
              v-model="aiStore.form.language"
              :options="chatStore.languages"
              valueProp="name"
              input-label="name"
              placeholder="Language"
            />
            <InputFieldError
              v-if="aiStore.errors?.language"
              :message="aiStore.errors?.language[0]"
            />
          </div>
          <div>
            <label class="label mb-1">{{ trans('Tone') }}*</label>
            <Multiselect
              mode="single"
              class="multiselect-dark"
              v-model="aiStore.form.tone"
              :options="aiStore.tones"
              valueProp=""
              placeholder="Tone"
            />
            <InputFieldError v-if="aiStore.errors?.tone" :message="aiStore.errors?.tone[0]" />
          </div>
        </div>

        <!-- fields -->
        <div>
          <div v-for="(field, i) in aiStore.form.fields" :key="i">
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
            <InputFieldError v-if="aiStore.hasError" :message="aiStore.fieldErrors[field.name]" />
            <InputFieldError
              v-if="!aiStore.hasError && aiStore.errors && aiStore.errors['fields.' + i + '.value']"
              :message="aiStore.errors['fields.' + i + '.value'][0]"
            />
          </div>
        </div>

        <div class="flex items-center gap-5">
          <div class="flex-1">
            <label class="label mb-1">{{ trans('Maximum Word Limit') }}</label>
            <input
              v-model.number="aiStore.form.max_token"
              type="text"
              class="input h-10"
              placeholder="Maximum Length"
            />
            <InputFieldError
              v-if="aiStore.errors?.max_token"
              :message="aiStore.errors?.max_token[0]"
            />
          </div>
          <div class="flex-1">
            <label class="label mb-1">{{ trans('Creativity Level') }}</label>
            <Multiselect
              mode="single"
              class="multiselect-dark"
              v-model="aiStore.form.creativity"
              :options="aiStore.creativityLevels"
              value-prop="value"
              inputLabel="label"
              placeholder="Creativity Level"
            />
            <InputFieldError
              v-if="aiStore.errors?.creativity"
              :message="aiStore.errors.creativity[0]"
            />
          </div>
        </div>

        <SpinnerBtn
          classes="btn btn-primary w-full py-3"
          :processing="aiStore.isProcessing"
          :btn-text="trans('Generate')"
        />
      </form>
      <div class="mt-4 flex flex-col gap-y-3">
        <div class="flex items-center gap-x-2">
          <input
            v-model="aiStore.documentName"
            type="text"
            class="input"
            placeholder="Untitled Document..."
          />
          <div class="flex">
            <button
              class="ms-1"
              type="button"
              @click="aiStore.downloadHTMLFile(aiStore.generatedText, aiStore.documentName)"
            >
              <Icon icon="bx:download" class="size-6" />
            </button>
          </div>
        </div>
        <div>
          <label class="label mb-1">{{ trans('Generated Text') }}</label>
          <textarea class="textarea" v-model="aiStore.generatedText" />
        </div>
        <div class="flex justify-end gap-2" v-if="aiStore.generatedText">
          <button
            class="btn btn-info btn-sm"
            type="button"
            @click="inputMessage.message = aiStore.generatedText"
          >
            <Icon icon="bx:pen" class="size-4" />
            <span>{{ trans('Use') }}</span>
          </button>
          <button
            class="btn btn-primary btn-sm"
            type="button"
            @click="copyToClipboard(aiStore.generatedText)"
          >
            <Icon icon="bx:copy" class="size-4" />
            <span>{{ trans('Copy') }}</span>
          </button>
        </div>
      </div>
    </div>
    <NoDataFound v-if="chatStore.aiTemplates?.length < 1" />
  </Modal>

  <!-- Asset Modal start -->
  <AssetModal />

  <!-- Location Modal start -->
  <Modal :header-state="true" header-title="Send Location Message" state="locationMessageModal">
    <form @submit.prevent="sendLocationMessage">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label mb-1">{{ trans('Latitude') }}</label>
          <input v-model="locationForm.latitude" type="text" class="input" placeholder="Latitude" />
        </div>
        <div>
          <label class="label mb-1">{{ trans('Longitude') }}</label>
          <input
            v-model="locationForm.longitude"
            type="text"
            class="input"
            placeholder="Longitude"
          />
        </div>
      </div>
      <SpinnerBtn
        :processing="locationForm.processing"
        :btn-text="trans('Send')"
        class="mt-3 w-full"
      />
    </form>
  </Modal>
</template>
