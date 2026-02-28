<script setup>
import { onMounted, onUnmounted } from 'vue'

import { Head } from '@inertiajs/vue3'
import ChatLayout from '@whatsappWeb/Pages/Chats/Layout/ChatLayout.vue'
import MessageContainer from '@whatsappWeb/Pages/Chats/Layout/MessageContainer.vue'
import Modals from '@whatsappWeb/Pages/Chats/Layout/Modals.vue'
import RightSidebar from '@whatsappWeb/Pages/Chats/Layout/RightSidebar.vue'
import NoConversation from '@/Components/Chats/NoConversation.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import sharedComposable from '@/Composables/sharedComposable'

const { authUser } = sharedComposable()

defineOptions({ layout: [UserLayout, ChatLayout] })

const props = defineProps([
  'platforms',
  'chat_templates',
  'quick_replies',
  'badges',
  'api_base_url',
  'languages',
  'module_features'
])

const chatStore = useChatStore()

onMounted(() => {
  chatStore.features = props.module_features
  chatStore.$patch({
    baseApiUrl: props.api_base_url ?? '',
    platforms: props.platforms ?? [],
    chatTemplates: props.chat_templates ?? [],
    quickReplies: props.quick_replies ?? [],
    aiTemplates: props.ai_templates ?? [],
    badges: props.badges ?? [],
    languages: props.languages ?? [],
    conversations: []
  })

  chatStore.platforms.forEach((platform) => {
    chatStore.fetchConversations(platform)
  })

  chatStore.connectWebSocket(`live-chat.whatsapp-web.${authUser.value?.active_workspace?.owner_id}`)
})
</script>

<template>
  <Head :title="trans('Whatsapp Conversations')" />
  <Modals />
  <Head :title="trans('Chats')" />
  <template v-if="chatStore.hasActiveConversation">
    <MessageContainer />
    <RightSidebar />
    <Modals />
  </template>
  <NoConversation v-else />
</template>
