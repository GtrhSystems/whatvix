<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import PlatformSettingModal from '@/Components/User/Platforms/PlatformSettingModal.vue'
import { useModalStore } from '@/Store/modalStore'
import { ref } from 'vue'
import NotificationRing from '@/Components/Chats/NotificationRing.vue'
import { router } from '@inertiajs/vue3'
import toastComposable from '@/Composables/toastComposable'
const modalStore = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['platforms', 'aiTrainings', 'autoResponses'])
const { deleteRow, badgeClass, textExcerpt } = sharedComposable()

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

const verifyNumber = (uuid) => {
  axios.get(route('user.whatsapp-web.api.platforms.check-verification', uuid)).then((res) => {
    if (res.data.exists) {
      toastComposable.success('Number is verified')
      router.reload()
    } else {
      toastComposable.danger('Number not verified')
    }
  })
}
</script>

<template>
  <NotificationRing module="whatsapp-web" />
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
        <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
          <div class="flex items-center gap-4">
            <div class="flex flex-col">
              <p class="font-bold capitalize text-gray-900 dark:text-gray-100">
                {{ textExcerpt(platform.name, 20) }}
              </p>
              <span class="text-xs">{{ platform.meta?.phone_number }}</span>
              <span
                class="mt-1 inline-flex w-fit items-center rounded-full px-2 py-px text-xs lowercase"
                :class="{
                  'bg-emerald-100 text-emerald-700':
                    platform.status === 'connected' || platform.status === 'verified',
                  'bg-red-100 text-red-700': ['disconnected', 'inactive', 'unverified'].includes(
                    platform.status
                  ),
                  'bg-yellow-100 text-yellow-700': platform.status === 'wait_for',
                  'bg-blue-100 text-blue-700': platform.status === 'pulling_wa_data'
                }"
              >
                <span
                  class="mr-1 mt-0.5 h-2 w-2 rounded-full"
                  :class="{
                    'bg-emerald-500':
                      platform.status === 'connected' || platform.status === 'verified',
                    'bg-red-500': ['disconnected', 'inactive', 'unverified'].includes(
                      platform.status
                    ),
                    'bg-yellow-500': platform.status === 'wait_for',
                    'bg-blue-500': platform.status === 'pulling_wa_data'
                  }"
                ></span>
                {{ platform.status }}
              </span>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-around gap-2">
            <Link
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-blue-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-blue-400"
              :href="route('user.whatsapp-web.platforms.conversations.index', platform.uuid)"
              title="View Messages"
            >
              <Icon icon="bx:message-square-dots" class="text-xl" />
            </Link>

            <Link
              title="Reconnect"
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-indigo-400"
              :href="route('user.whatsapp-web.platforms.connection', platform.uuid)"
            >
              <Icon icon="bx:refresh" class="text-xl" />
            </Link>

            <button
              v-if="!platform.meta?.verified"
              title="Verify Number"
              type="button"
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-gray-100 hover:text-emerald-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-emerald-400"
              @click="verifyNumber(platform.uuid)"
            >
              <Icon icon="bx:check-shield" class="text-xl" />
            </button>

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
              @click="deleteRow(route('user.whatsapp-web.platforms.destroy', platform))"
              class="inline-flex items-center justify-center rounded-lg bg-gray-50 p-2.5 text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-red-900/20 dark:hover:text-red-400"
            >
              <Icon icon="bx:trash" class="text-xl" />
            </button>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-3">
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="text-xs font-medium text-gray-500">{{ trans('Welcome Message') }}</span>
            <span
              class="text-sm font-semibold"
              :class="platform.meta?.send_welcome_message ? 'text-emerald-600' : 'text-red-600'"
              >{{
                platform.meta?.send_welcome_message ? trans('Enabled') : trans('Disabled')
              }}</span
            >
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="text-xs font-medium text-gray-500">{{ trans('Auto Reply') }}</span>
            <span
              class="text-sm font-semibold"
              :class="platform.meta?.send_auto_reply ? 'text-emerald-600' : 'text-red-600'"
            >
              {{ platform.meta?.send_auto_reply ? trans('Enabled') : trans('Disabled') }}
            </span>
          </div>
          <div class="flex flex-col rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <span class="text-xs font-medium text-gray-500">{{ trans('Auto Reply Method') }}</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{
              platform.meta?.auto_reply_method
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
    :autoReplyMethods="['default', 'auto_response', 'trained_ai']"
    :trainedAiModels="aiTrainings"
    routeName="user.whatsapp-web.platforms.update"
    :autoResponses="autoResponses"
    module="whatsapp-web"
  />
</template>
