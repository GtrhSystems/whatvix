<script setup>
import { useForm } from '@inertiajs/vue3'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'

const chatStore = useChatStore()

const toggleAutoReply = () => {
  let form = useForm({
    update_type: 'auto_reply'
  })

  form.put(`/user/conversations/${chatStore.activeConversation?.id}`)
}
</script>

<template>
  <!-- Chat Wrapper Header Starts -->
  <div
    class="flex items-center justify-between border-b border-b-slate-200 p-3 dark:border-b-dark-900"
  >
    <!-- Avatar and Menu Button Start -->
    <div class="flex items-center justify-start gap-x-3">
      <button
        @click="chatStore.toggleLeftSidebar"
        type="button"
        class="text-slate-500 hover:text-slate-700 focus:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 dark:focus:text-slate-300 xl:hidden"
      >
        <Icon icon="bx:menu" class="text-2xl" />
      </button>

      <button type="button" class="avatar avatar-circle" @click="chatStore.toggleRightSidebar">
        <img
          class="avatar-img relative"
          :src="chatStore.getConversationProfilePic(chatStore.activeConversation)"
        />
        <div class="absolute -right-2 bottom-0 rounded-full bg-white p-1">
          <Icon :icon="'ri:whatsapp-fill'" class="text-green-600" />
        </div>
      </button>

      <div>
        <h6 class="whitespace-nowrap text-sm font-medium text-slate-700 dark:text-slate-100">
          {{ chatStore.activeConversation.name }}
          <span
            class="rounded p-0 px-2 text-sm text-white"
            :style="{
              background: chatStore.getBadge(chatStore.activeConversation?.badge_id)?.color
            }"
          >
            {{ chatStore.getBadge(chatStore.activeConversation?.badge_id)?.text }}
          </span>
        </h6>
        <p class="truncate text-sm font-normal text-slate-500 dark:text-slate-400">
          <template v-if="chatStore.getConversationType() === 'group'">
            <span v-if="chatStore.loading.groupMetadata">Loading...</span>
            <span v-else>
              {{ chatStore.activeConversation.groupMetadata?.size ?? 0 }} Members
            </span>
          </template>
          <template v-else>
            {{ chatStore.activeConversation.id.split('@')[0].substring(2) }}
          </template>
        </p>
      </div>
    </div>
    <!-- Avatar and Menu Button End -->

    <!-- Actions and More Dropdown Start -->
    <div class="flex flex-wrap items-center gap-1">
      <label for="toggle-unchecked-input" class="toggle">
        <input
          @change="toggleAutoReply"
          class="toggle-input peer sr-only"
          id="toggle-unchecked-input"
          type="checkbox"
          :checked="chatStore.activeConversation.auto_reply_enabled"
        />
        <div class="toggle-body"></div>
        <span class="label">Auto Reply</span>
      </label>

      <button class="btn" type="button" @click="chatStore.toggleRightSidebar">
        <Icon icon="bx:bxs-info-circle" class="text-2xl text-primary-500" />
      </button>
    </div>
    <!-- Actions and More Dropdown End -->
  </div>
  <!-- Chat Wrapper Header Ends -->
</template>
