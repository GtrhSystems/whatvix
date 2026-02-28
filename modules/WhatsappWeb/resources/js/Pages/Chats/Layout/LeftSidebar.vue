<script setup>
import { onMounted, ref } from 'vue'

import HollowDotsSpinner from '@/Components/HollowDotsSpinner.vue'
import IntersectionObserver from '@/Components/IntersectionObserver.vue'
import sharedComposable from '@/Composables/sharedComposable'
import { useChatStore } from '@whatsappWeb/Stores/chatStore'
import moment from 'moment'
import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

const props = defineProps({
  conversations: {
    type: Array,
    default: []
  }
})

const { debounce, pickBy } = sharedComposable()

const chatStore = useChatStore()

const scrollContainerRef = ref(null)
const endOfScrollRef = ref(null)

onMounted(() => {
  let searchFormQueries = new URLSearchParams(window.location.search)
  chatStore.searchForm.badge_id = searchFormQueries.get('badge_id') || ''
  chatStore.searchForm.customer_name = searchFormQueries.get('customer_name') || ''
})
</script>
<template>
  <!-- Chat Left Sidebar Starts -->
  <div
    id="chat-sidebar"
    class="absolute z-30 h-[calc(100vh-11rem)] w-3/4 rounded-md bg-white p-3 transition-all duration-300 dark:bg-dark-800 sm:w-72 lg:relative xl:w-3/12"
    :class="[
      chatStore.leftSidebar.isOpen ? 'translate-x-0' : 'translate-x-[-110%]',
      'lg:translate-x-0'
    ]"
  >
    <!-- Chat Sidebar Header Starts -->
    <div class="relative mb-3">
      <div class="mb-2 flex items-center gap-2 text-nowrap">
        <select
          class="select"
          v-model="chatStore.searchForm.badge_id"
          @change="getFilteredConversations"
        >
          <option value="">{{ trans('Filter by badge') }}</option>
          <option v-for="badge in chatStore.badges" :value="badge.id" :key="badge.id">
            {{ badge.text }}
          </option>
        </select>
        <button class="btn btn-primary" @click="modalStore.open('badgeModal')">+</button>
      </div>
      <form
        @submit.prevent=""
        class="group flex h-10 w-full items-center overflow-hidden rounded-primary border border-transparent bg-slate-100 shadow-sm focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500 dark:border-transparent dark:bg-dark-900 dark:focus-within:border-primary-500"
      >
        <div class="mb-2"></div>
        <div class="flex h-full items-center px-3">
          <Icon
            icon="fe:search"
            class="text-lg text-slate-400 group-focus-within:text-primary-500"
          />
        </div>
        <input
          class="h-full w-full border-transparent bg-transparent px-0 text-sm placeholder-slate-400 placeholder:text-sm focus:border-transparent focus:outline-none focus:ring-0"
          type="text"
          v-model="chatStore.searchForm.customer_name"
          placeholder="Search"
        />
      </form>

      <!-- Chat Sidebar Close Button Starts -->
      <button
        id="chat-btn-hide-sidebar"
        type="button"
        v-if="chatStore.leftSidebar.isOpen"
        @click="chatStore.toggleLeftSidebar"
        class="absolute left-full top-1 z-20 inline-flex h-8 w-8 translate-x-4 items-center justify-center rounded-full bg-black/60 text-white dark:text-slate-300 xl:hidden"
      >
        <Icon icon="fe:close" />
      </button>
      <!-- Chat Sidebar Close Button Ends -->
    </div>

    <!-- Chat Sidebar Body Starts -->
    <div class="styled-scrollbar h-[88%] overflow-y-auto pb-4">
      <ul id="chat-list" ref="scrollContainerRef">
        <li
          v-for="chat in chatStore.filteredConversations"
          class="group"
          :class="{ active: chatStore.activeConversation?.id == chat.id }"
          :key="chat.id"
        >
          <a
            @click="chatStore.setActiveConversation(chat)"
            class="inline-flex w-full items-start justify-between rounded-md bg-transparent px-3 py-3 transition-colors duration-150 hover:bg-slate-100 group-[.active]:bg-slate-100 dark:hover:bg-[#242424] group-[.active]:dark:bg-dark-900"
          >
            <div class="avatar avatar-circle shrink-0">
              <img class="avatar-img relative" :src="chatStore.getConversationProfilePic(chat)" />
              <div class="absolute -right-2 bottom-0 rounded-full bg-white p-1">
                <Icon :icon="`ri:whatsapp-fill`" class="text-[#00c67e]" />
              </div>
            </div>
            <div class="ml-4 mr-2 flex-grow overflow-x-hidden">
              <p
                class="whitespace-nowrap text-sm text-slate-800 dark:text-slate-100"
                :class="[chat.unreadCount > 0 ? 'font-semibold' : 'font-medium']"
              >
                {{ chat.name ?? chat.id.split('@')[0].substring(2) ?? 'Unknown' }}
                <span
                  class="rounded p-0 px-2 text-sm text-white"
                  :style="{ background: chatStore.getBadge(chat?.badge_id)?.color }"
                >
                  {{ chatStore.getBadge(chat?.badge_id)?.text }}
                </span>
              </p>
              <span class="truncate text-xs font-normal text-gray-500">
                {{
                  chat.conversationTimestamp
                    ? moment.unix(chat.conversationTimestamp).local().fromNow()
                    : ''
                }}
              </span>
            </div>

            <div class="flex flex-col items-end">
              <span
                v-if="chat.unreadCount > 0"
                class="flex h-5 w-5 items-center justify-center rounded-full bg-danger-500 text-xs font-normal text-white"
              >
                {{ chat.unreadCount ?? '' }}
              </span>
            </div>
          </a>
        </li>
        <li class="py-1">
          <IntersectionObserver
            :intersectionStart="0"
            :afterIntersection="chatStore.loadMoreConversations"
            :loader="chatStore.loading.conversations"
          />
        </li>
        <li
          v-if="
            chatStore.filteredConversations ||
            chatStore.searchForm.customer_name.length > 0 ||
            chatStore.searchForm.badge_id > 0
          "
        >
          <HollowDotsSpinner class="mx-auto" v-if="chatStore.loading.searching" />
          <div
            v-else-if="chatStore.filteredConversations.length == 0"
            class="text-center text-sm text-slate-400"
          >
            {{ trans('No results found') }}
          </div>
        </li>
      </ul>
    </div>
    <!-- Chat Sidebar Body Ends -->
  </div>
  <div
    @click="chatStore.toggleLeftSidebar"
    v-if="chatStore.leftSidebar.isOpen"
    class="absolute bottom-0 left-0 right-0 top-0 z-10 h-full w-full bg-black/20 transition-colors duration-300 ease-in-out xl:hidden"
  ></div>
</template>
