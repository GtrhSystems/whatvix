<script setup>
import { ref } from 'vue'

import moment from 'moment'

import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useChatStore } from '@/Store/chatStore'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()

const isNoteEditing = ref(false)

const chatStore = useChatStore()

const profilePictureRef = ref(null)
const customerDetailsForm = useForm({
  picture: '',
  note: chatStore.activeConversation?.meta?.note ?? '',
  update_type: null,
  _method: 'put'
})

const uploadProfilePicture = (e) => {
  customerDetailsForm.picture = e.target.files[0]
  customerDetailsForm.update_type = 'profile_picture'
  updateCustomer()
}

const updateNote = () => {
  customerDetailsForm.update_type = 'note'
  updateCustomer()
}

const updateCustomer = () => {
  customerDetailsForm.post(route('user.conversations.update', chatStore.activeConversationId), {
    onSuccess: () => {
      isNoteEditing.value = false
      if (customerDetailsForm.update_type === 'profile_picture') {
        chatStore.activeConversation.customer.picture = URL.createObjectURL(
          customerDetailsForm.picture
        )
      }
    }
  })
}

const badgeRemoveForm = useForm({})
const removeChatBadge = (conversationId) => {
  badgeRemoveForm.delete(route('user.conversations.badges.destroy', conversationId), {
    onSuccess: () => {
      chatStore.activeConversation.badge_id = null
    }
  })
}

const openMessageSearchModal = () => {
  modalStore.open('findMessageModal')
  console.log(chatStore.chatSearchInputRef)
}
</script>

<template>
  <!-- Chat detailSidebar Starts -->

  <div
    class="card absolute right-0 z-20 ml-2 h-[calc(100vh-11rem)] w-6/12 rounded-md p-1 transition-all duration-300 lg:w-3/12"
    :class="[chatStore.rightSidebar.isOpen ? '-translate-x-0' : 'translate-x-[125%]']"
  >
    <button
      type="button"
      @click="chatStore.toggleRightSidebar"
      class="absolute left-0 top-5 z-20 inline-flex h-8 w-8 -translate-x-10 items-center justify-center rounded-full bg-black/50 text-white dark:text-slate-300"
    >
      <Icon icon="fe:close" />
    </button>
    <h6 class="border-b border-gray-100 py-4 pl-3 dark:border-dark-700">Details</h6>

    <div class="mt-3 flex flex-col items-center justify-center">
      <button class="group relative h-14 w-14" type="button" @click="profilePictureRef.click()">
        <img
          class="h-full w-full rounded-full border object-contain object-center"
          :src="chatStore.activeConversation?.customer?.picture"
          alt="profile-img"
        />
        <div
          class="absolute inset-0 z-50 flex h-full w-full items-center justify-center rounded-full bg-gray-800 bg-opacity-30 opacity-0 transition-opacity group-hover:opacity-100"
        >
          <Icon icon="bx:camera" class="text-lg text-gray-300" />
        </div>
        <input
          ref="profilePictureRef"
          class="hidden"
          type="file"
          @change="uploadProfilePicture"
          accept="image/jpg, image/jpeg, image/png"
          placeholder="change"
        />
      </button>

      <div class="mt-2 flex flex-col items-center text-sm">
        <p class="font-semibold">
          {{ chatStore.activeConversation?.customer?.name }}
        </p>
        <p class="text-gray-500">
          {{ chatStore.activeConversation?.customer?.uuid }}
        </p>
      </div>

      <div class="mt-2">
        <p
          class="cursor-pointer text-sm font-semibold text-primary-600"
          @click="openMessageSearchModal"
        >
          {{ trans('Search Message') }}
        </p>
      </div>
    </div>
    <div class="mt-4 space-y-4 p-4">
      <div class="flex items-center gap-2">
        <Icon icon="bx:badge" class="mt-1" />
        <span class="text-sm">{{ trans('Badge') }}:</span>
        <template v-if="chatStore.activeConversation?.badge_id">
          <span
            class="rounded p-0 px-2 text-sm text-white"
            :style="{
              background: chatStore.getBadge(chatStore.activeConversation?.badge_id)?.color
            }"
          >
            {{ chatStore.getBadge(chatStore.activeConversation?.badge_id)?.text }}
          </span>
          <Icon icon="bx:edit" class="cursor-pointer" @click="modalStore.open('addBadgeModal')" />
          <Icon
            icon="bx:trash"
            @click="removeChatBadge(chatStore.activeConversationId)"
            class="cursor-pointer text-danger-700"
          />
        </template>
        <Icon
          v-else
          icon="bx:plus"
          @click="modalStore.open('addBadgeModal')"
          class="cursor-pointer"
        />
      </div>
      <div class="flex items-center gap-2">
        <Icon icon="bx:message" class="mt-1" />
        <span class="text-sm">{{ trans('Conversation ID') }}</span>
        <span class="text-sm font-bold"> {{ chatStore.activeConversationId }}</span>
      </div>
      <div class="flex items-center gap-2">
        <Icon icon="ic:baseline-whatsapp" class="mt-1" />
        <span class="text-sm">{{ trans('Source') }}</span>
        <span class="text-sm font-bold capitalize">
          {{ chatStore.activeConversation?.module }}</span
        >
      </div>

      <div class="flex items-center gap-2">
        <Icon icon="bx:calendar" class="mt-px" />
        <span class="text-sm">{{ trans('Creation Time') }}</span>
        <span class="text-sm font-bold">
          {{ moment(chatStore.activeConversation?.created_at).format('DD/MM/YYYY') }}</span
        >
      </div>
      <div class="flex items-center gap-2">
        <Icon icon="bx:phone" class="mt-px" />
        <span class="text-sm">{{ trans('Phone Number') }}</span>
        <span class="text-sm font-bold"> {{ chatStore.activeConversation?.customer?.uuid }}</span>
      </div>

      <div>
        <div class="flex justify-between">
          <p>{{ trans('Note') }}</p>
          <Icon
            v-if="isNoteEditing"
            icon="bx:x"
            class="cursor-pointer text-xl"
            @click="isNoteEditing = false"
          />
          <Icon v-else icon="bx:edit" class="cursor-pointer" @click="isNoteEditing = true" />
        </div>

        <div v-if="isNoteEditing">
          <textarea
            class="textarea"
            id="note"
            v-model="customerDetailsForm.note"
            placeholder="Enter note here"
          >
          </textarea>
          <SpinnerBtn
            :processing="customerDetailsForm.processing"
            @click="updateNote"
            :btn-text="trans('Save')"
            class="w-full"
          />
        </div>

        <div v-else>
          <p v-if="customerDetailsForm.note" class="alert alert-info">
            {{ customerDetailsForm.note }}
          </p>
          <div v-else>
            <div class="text-center">
              <p class="small mb-2 italic text-gray-500">{{ trans('Note not set yet') }}</p>
              <button class="btn btn-sm btn-primary" @click="isNoteEditing = true">
                {{ trans('Set a Note') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div
    @click="chatStore.toggleRightSidebar"
    v-if="chatStore.rightSidebar.isOpen"
    class="absolute inset-0 z-0 h-full w-full bg-black/20 transition-colors duration-300 ease-in-out"
  ></div>
</template>
