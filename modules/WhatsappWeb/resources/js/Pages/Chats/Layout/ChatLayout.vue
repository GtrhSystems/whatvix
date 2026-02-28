<script setup>
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import LeftSidebar from './LeftSidebar.vue'
const chatStore = useChatStore()

defineOptions({ layout: UserLayout })
</script>

<template>
  <div class="alert alert-danger mb-2" v-if="chatStore.disconnected">
    {{ trans('Connection lost, cannot connect to whatsapp server') }}
  </div>
  <div class="relative flex w-full gap-x-3 overflow-hidden">
    <LeftSidebar />
    <slot />
  </div>
</template>
