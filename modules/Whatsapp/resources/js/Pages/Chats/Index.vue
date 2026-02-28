<script setup>
import { onMounted } from 'vue'

import { Head } from '@inertiajs/vue3'
import ChatLayout from '@/Components/Chats/ChatLayout.vue'
import MessageContainer from '@/Components/Chats/MessageContainer.vue'
import Modals from '@/Components/Chats/Modals.vue'
import RightSidebar from '@/Components/Chats/RightSidebar.vue'
import NoConversation from '@/Components/Chats/NoConversation.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useChatStore } from '@/Store/chatStore'

defineOptions({ layout: [UserLayout, ChatLayout] })

const props = defineProps([
  'id',
  'conversations',
  'chat_templates',
  'quick_replies',
  'ai_templates',
  'badges',
  'platform',
  'languages',
  'module_features'
])

const chatStore = useChatStore()

onMounted(() => {
  chatStore.features = props.module_features

  chatStore.activeConversationId = null

  // check if url has badge_id property
  const urlParams = new URLSearchParams(window.location.search)
  const searchParams = urlParams.get('customer_name') || urlParams.get('q') || null

  chatStore.conversations = props.conversations || []

  if (props.id) {
    chatStore.setActiveConversation(props.id)
  }

  if (props.platform) {
    chatStore.platform = props.platform
  }

  chatStore.chatTemplates = props.chat_templates || []
  chatStore.quickReplies = props.quick_replies || []
  chatStore.aiTemplates = props.ai_templates || []
  chatStore.badges = props.badges || []
  chatStore.languages = props.languages || []

  if (props.platform?.owner_id) {
    chatStore.connectWebSocket(`live-chat.whatsapp.${props.platform.owner_id}`)
  }
})
</script>

<template>
  <Head :title="trans('Chats')" />
  <template v-if="chatStore.hasActiveConversation">
    <MessageContainer />
    <RightSidebar />
    <Modals />
  </template>
  <NoConversation v-else />
</template>
