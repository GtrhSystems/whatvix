<script setup>
import { ref, computed } from 'vue'

import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import PlatformSettingModal from '@/Components/User/Platforms/PlatformSettingModal.vue'
import sharedComposable from '@/Composables/sharedComposable'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import NotificationRing from '@/Components/Chats/NotificationRing.vue'

const modalStore = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['platforms', 'aiTrainings', 'autoResponses', 'flows'])
const { deleteRow, textExcerpt } = sharedComposable()

const selectedPlatform = ref({})

const openPlatformSettingModal = (bot) => {
  selectedPlatform.value = bot
  modalStore.open('platformSettingModal')
}
const filterOptions = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Status',
    value: 'status'
  }
]

const platformConfigModalFields = computed(() => {
  let fields = ['name', 'phone_number_id', 'webhook_url', 'verify_token']
  let isESModulePlatform = selectedPlatform.value?.meta?.type !== 'whatsapp_es'
  if (isESModulePlatform) {
    fields.push('access_token')
  }
  return fields
})
</script>

<template>
  <NotificationRing />
  <FilterDropdown :options="filterOptions" />
  <div class="mt-4 flex w-full flex-col gap-4">
    <div
      v-if="platforms && platforms?.data.length"
      class="grid grid-cols-1 gap-4 xl:grid-cols-2 2xl:grid-cols-3"
    >
      <div
        v-for="(platform, index) in platforms.data"
        :key="index"
        class="card card-body group flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
      >
        <div class="flex items-start justify-between">
          <div class="flex flex-col">
            <h4
              :title="platform.name"
              class="max-w-52 truncate font-semibold capitalize text-gray-900 dark:text-gray-100 2xl:text-lg"
            >
              {{ platform.name }}
            </h4>
            <span
              class="mt-1 inline-flex w-fit items-center rounded-full px-2 py-px text-xs lowercase"
              :class="{
                'bg-emerald-100 text-emerald-700': platform.meta?.webhook_connected,
                'bg-red-100 text-red-700': !platform.meta?.webhook_connected
              }"
            >
              <span
                class="mr-1 mt-0.5 h-2 w-2 rounded-full"
                :class="{
                  'bg-emerald-500': platform.meta?.webhook_connected,
                  'bg-red-500': !platform.meta?.webhook_connected
                }"
              ></span>
              {{ platform.meta?.webhook_connected ? 'Connected' : 'Disconnected' }}
            </span>
          </div>

          <div class="flex gap-2">
            <Link
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-blue-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-blue-400"
              :href="route('user.whatsapp.platforms.show', platform.uuid)"
              title="View Messages"
            >
              <Icon icon="bx:message-square-dots" class="text-xl" />
            </Link>
            <Link
              title="QR Codes"
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-indigo-400"
              :href="route('user.whatsapp.qr-codes.index', platform.uuid)"
            >
              <Icon icon="bx:qr" />
            </Link>
            <Link
              title="Message Logs"
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-indigo-400"
              :href="route('user.whatsapp.platforms.logs', platform.uuid)"
            >
              <Icon icon="bx:detail" class="text-xl" />
            </Link>
            <button
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-emerald-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-emerald-400"
              @click="() => openPlatformSettingModal(platform)"
              title="Settings"
            >
              <Icon icon="bx:cog" class="text-xl" />
            </button>
            <button
              title="Delete Platform"
              type="button"
              @click="deleteRow(route('user.whatsapp.platforms.destroy', platform))"
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-red-900/20 dark:hover:text-red-400"
            >
              <Icon icon="bx:trash" class="text-xl" />
            </button>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3">
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="mb-1 text-xs font-medium text-gray-500">{{ trans('Auto Reply') }}</span>
            <span
              class="text-sm font-semibold"
              :class="platform.meta?.send_auto_reply ? 'text-emerald-600' : 'text-red-600'"
            >
              {{ platform.meta?.send_auto_reply ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="mb-1 text-xs font-medium text-gray-500">{{ trans('Reply Method') }}</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{
              platform.meta?.auto_reply_method
            }}</span>
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="mb-1 text-xs font-medium text-gray-500">{{ trans('Dataset') }}</span>
            <span class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{
              textExcerpt(platform.meta?.auto_reply_dataset_name, 15)
            }}</span>
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="mb-1 text-xs font-medium text-gray-500">{{
              trans('Welcome Message')
            }}</span>
            <span
              class="text-sm font-semibold"
              :class="platform.meta?.send_welcome_message ? 'text-emerald-600' : 'text-red-600'"
            >
              {{ platform.meta?.send_welcome_message ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="mb-1 text-xs font-medium text-gray-500">{{
              trans('Phone Number ID')
            }}</span>
            <span class="truncate text-xs font-semibold text-gray-900 dark:text-gray-100">{{
              platform.meta?.phone_number_id
            }}</span>
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="mb-1 text-xs font-medium text-gray-500">{{ trans('WABA ID') }}</span>
            <span class="truncate text-xs font-semibold text-gray-900 dark:text-gray-100">{{
              platform.meta?.business_account_id
            }}</span>
          </div>
        </div>

        <div
          v-if="platform.meta?.send_welcome_message"
          class="mt-2 rounded-lg bg-purple-50 p-3 dark:bg-purple-900/20"
        >
          <div class="flex items-start gap-2">
            <Icon icon="bx:message-rounded-dots" class="mt-0.5 text-lg text-purple-500" />
            <div class="flex flex-col">
              <span class="text-xs font-medium text-purple-700 dark:text-purple-400">{{
                trans('Welcome Message')
              }}</span>
              <span class="text-sm text-purple-900 dark:text-purple-100">{{
                platform.meta?.welcome_message_template
              }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <NoDataFound v-else />

    <div class="w-full">
      <Paginate v-if="platforms?.data?.length" :links="platforms.links" />
    </div>
  </div>

  <PlatformSettingModal
    :platform="selectedPlatform"
    :autoReplyMethods="['default', 'trained_ai', 'chat_flow', 'auto_response']"
    :trainedAiModels="aiTrainings"
    :chatFlows="flows"
    :autoResponses="autoResponses"
    routeName="user.whatsapp.platforms.update"
    :fields="platformConfigModalFields"
  />
</template>
