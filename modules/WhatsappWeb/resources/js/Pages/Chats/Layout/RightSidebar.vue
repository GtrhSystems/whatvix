<script setup>
import { ref } from 'vue'

import moment from 'moment'

import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import { useModalStore } from '@/Store/modalStore'
import sharedComposable from '@/Composables/sharedComposable'

const { copyToClipboard } = sharedComposable()

const modalStore = useModalStore()

const isNoteEditing = ref(false)
const isNameEditing = ref(false)

const chatStore = useChatStore()

const customerDetailsForm = useForm({
  name:
    chatStore.activeConversation?.name ??
    chatStore.activeConversation.id.split('@')[0].substring(2),
  note: chatStore.activeConversation.description,
  picture: '',
  update_type: null,
  module: 'whatsapp-web',
  _method: 'put'
})

const updateNote = () => {
  customerDetailsForm.update_type = 'note'
  updateCustomer()
}

const updateCustomer = () => {
  customerDetailsForm.post(route('user.conversations.update', chatStore.activeConversation?.id), {
    onSuccess: () => {
      isNoteEditing.value = false
      isNameEditing.value = false
      if (customerDetailsForm.update_type === 'profile_picture') {
        chatStore.activeConversation.customer.picture = URL.createObjectURL(
          customerDetailsForm.picture
        )
      }
      if (customerDetailsForm.update_type === 'name') {
        chatStore.activeConversation.name = customerDetailsForm.name
      }

      if (customerDetailsForm.update_type === 'note') {
        chatStore.activeConversation.description = customerDetailsForm.note
      }
    }
  })
}

const updateCustomerName = () => {
  customerDetailsForm.update_type = 'name'
  updateCustomer()
}

const removeChatBadge = (conversationId) => {
  useForm({}).delete(route('user.conversations.badges.destroy', conversationId), {
    onSuccess: () => {
      chatStore.activeConversation.badge_id = null
    }
  })
}
</script>

<template>
  <!-- Chat detailSidebar Starts -->
  <div
    class="card absolute right-0 z-20 ml-2 h-[calc(100vh-11rem)] w-6/12 rounded-md p-1 transition-all duration-300 lg:w-3/12"
    :class="[chatStore.rightSidebar.isOpen ? '-translate-x-0' : 'translate-x-[120%]']"
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
      <button class="group relative h-14 w-14" type="button">
        <img
          class="h-full w-full rounded-full border object-contain object-center"
          :src="chatStore.getConversationProfilePic()"
          alt="profile-img"
        />
      </button>

      <button
        type="button"
        class="btn btn-outline-primary btn-sm mt-2"
        @click="chatStore.getChatProfilePic"
      >
        {{ trans('Sync Profile') }}
      </button>

      <div class="mt-2 flex flex-col items-center text-sm">
        <form @submit.prevent="updateCustomerName" class="flex items-center gap-2 text-lg">
          <span v-if="!isNameEditing" class="font-semibold">
            {{
              chatStore.activeConversation?.name ??
              chatStore.activeConversation.id.split('@')[0].substring(2)
            }}
          </span>
          <input v-else type="text" class="input" v-model="customerDetailsForm.name" />
          <Icon
            v-if="!isNameEditing"
            icon="bx:edit"
            class="cursor-pointer text-primary-500"
            @click="isNameEditing = true"
          />
          <SpinnerBtn
            v-else
            class="btn btn-primary p-3"
            @click="updateCustomerName"
            :processing="customerDetailsForm.processing"
          >
            <Icon icon="bx:check" />
          </SpinnerBtn>
        </form>
        <template v-if="chatStore.getConversationType() === 'number'">
          {{ chatStore.activeConversation.id.split('@')[0].substring(2) }}
        </template>
        <template v-if="chatStore.getConversationType() === 'group'">
          <p class="text-gray-500">
            {{ chatStore.activeConversation.groupMetadata?.desc }}
          </p>

          <p class="text-gray-500">
            <span v-if="chatStore.loading.groupMetadata">{{ trans('Loading...') }}</span>
            <span v-else>
              {{ chatStore.activeConversation.groupMetadata?.size ?? 0 }} {{ trans('Members') }}
            </span>
          </p>

          <div class="w-full">
            <ul>
              <li
                v-for="participant in chatStore.activeConversation.groupMetadata?.participants"
                :key="participant.id"
              >
                {{ participant.id.split('@')[0].substring(2) }}
              </li>
            </ul>

            <button
              class="btn btn-sm btn-secondary w-full"
              type="button"
              @click="
                copyToClipboard(
                  chatStore.activeConversation.groupMetadata?.participants
                    .map((participant) => participant.id)
                    .join(', ')
                )
              "
            >
              {{ trans('Copy') }}
            </button>
          </div>
        </template>
      </div>
    </div>
    <div class="space-y-4 p-4 text-center">
      <div>
        <div class="flex items-center gap-2">
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
              @click="removeChatBadge(chatStore.activeConversation?.id)"
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
            v-model="customerDetailsForm.note"
            placeholder="Enter note here"
          ></textarea>
          <SpinnerBtn
            :processing="customerDetailsForm.processing"
            @click="updateNote"
            :btn-text="trans('Save')"
            class="w-full"
          />
        </div>

        <div v-else>
          <p v-if="chatStore.activeConversation?.description" class="alert alert-info">
            {{ chatStore.activeConversation?.description }}
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
  <!-- Chat detailSidebar Ends -->
</template>
