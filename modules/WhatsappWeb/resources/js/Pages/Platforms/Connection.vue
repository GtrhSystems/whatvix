<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

import ErrorWarning from '@whatsappWeb/Components/ErrorWarning.vue'
import HollowDotsSpinner from '@/Components/HollowDotsSpinner.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import toast from '@/Composables/toastComposable'
import trans from '@/Composables/transComposable'
import Modal from '@/Components/Dashboard/Modal.vue'
import axios from 'axios'
import { modal } from '@/Composables/actionModalComposable'

const modalStore = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['platform'])

const steps = [
  {
    icon: 'bx:bxl-whatsapp',
    text: trans('Open WhatsApp on your phone'),
    image: '/assets/images/scan-demo.gif'
  },
  {
    icon: 'bx:cog',
    text: trans('Tap Menu or Settings and select Linked Devices'),
    image: '/assets/images/scan-demo.gif'
  },
  {
    icon: 'bx:like',
    text: trans('Tap on Link a Device'),
    image: '/assets/images/scan-demo.gif'
  },
  {
    icon: 'material-symbols-light:qr-code-2-rounded',
    text: trans('Point your phone to this screen to capture the code'),
    image: '/assets/images/scan-demo.gif'
  }
]

// State variables
const isDeviceConnected = ref(false)
const stopTimeout = 120000 // 2 minutes
const statusTimeout = 5000 // 5 seconds
const loadingProgress = ref(0)
const errorCode = ref(null)
const shouldRequestStop = ref(false)
const platformSessionData = ref({
  qrCode: null,
  code: null,
  authType: 'qr_code',
  qrCodeLoader: true,
  codeLoader: false
})

// Interval and timeout references
let loadingBarIntervalId = null
let modalOpenTimeout = null
let statusIntervalId = null

// Functions to handle QR code loading
const loadQrCode = () => {
  platformSessionData.value.qrCodeLoader = true
  errorCode.value = null

  axios
    .get(route('user.whatsapp-web.api.platforms.connection', { uuid: props.platform.uuid }))
    .then((res) => {
      platformSessionData.value.qrCodeLoader = false
      platformSessionData.value.qrCode = res.data

      // Start status polling once QR code is loaded
      startStatusPolling()
    })
    .catch((err) => {
      platformSessionData.value.qrCodeLoader = false
      errorCode.value = err.status
      console.log(err)
    })
}

// Function to handle code-based authentication
const loadCode = () => {
  stopQrCodeInterval()
  platformSessionData.value.authType = 'code'
  platformSessionData.value.codeLoader = true
  errorCode.value = null

  axios
    .get(route('user.whatsapp-web.api.platforms.code', { uuid: props.platform.uuid }))
    .then((res) => {
      platformSessionData.value.codeLoader = false
      platformSessionData.value.code = res.data.code

      // Start status polling once code is loaded
      startStatusPolling()
    })
    .catch((err) => {
      platformSessionData.value.codeLoader = false
      platformSessionData.value.code = null
      errorCode.value = err.status
    })
}

// Status polling with setInterval
const startStatusPolling = () => {
  // Clear any existing interval
  if (statusIntervalId) {
    clearInterval(statusIntervalId)
  }

  // Set up the interval for checking status
  statusIntervalId = setInterval(() => {
    // Skip if we should stop or already connected
    if (shouldRequestStop.value || isDeviceConnected.value) return

    axios
      .get(route('user.whatsapp-web.api.platforms.check-status'), {
        params: { uuid: props.platform.uuid }
      })
      .then((res) => {
        if (res.data?.status === 'connected') {
          isDeviceConnected.value = true
          shouldRequestStop.value = true
          platformSessionData.value.qrCodeLoader = false

          // Stop checking once connected
          clearInterval(statusIntervalId)
          stopQrCodeInterval()
        } else {
          isDeviceConnected.value = false

          // If no QR code is loaded and we're in QR code mode, load it
          if (
            !platformSessionData.value.qrCodeLoader &&
            !platformSessionData.value.qrCode &&
            !shouldRequestStop.value &&
            platformSessionData.value.authType === 'qr_code'
          ) {
            loadQrCode()
            startQrCodeInterval()
          }
        }
      })
      .catch((err) => {
        platformSessionData.value.qrCodeLoader = false
        errorCode.value = err.status

        // Stop on severe errors
        if (err.status === 500) {
          clearInterval(statusIntervalId)
        }
      })
  }, statusTimeout)
}

// Loading bar management
const clearLoadingBarIntervalId = () => {
  if (loadingBarIntervalId) {
    clearInterval(loadingBarIntervalId)
    loadingBarIntervalId = null
  }
}

const startQrCodeInterval = () => {
  stopQrCodeInterval()
  loadingProgress.value = 0
  const startTime = Date.now()

  loadingBarIntervalId = setInterval(() => {
    const currentTime = Date.now()
    const timePassed = currentTime - startTime
    loadingProgress.value = Math.max(100 - (timePassed / stopTimeout) * 100, 0)

    if (loadingProgress.value <= 0) {
      clearLoadingBarIntervalId()
    }
  }, 200)

  modalOpenTimeout = setTimeout(() => {
    stopQrCodeInterval()
    if (
      !modalStore.getState('qrCodeModal') &&
      platformSessionData.value.authType === 'qr_code' &&
      !isDeviceConnected.value
    ) {
      modalStore.open('qrCodeModal')
      platformSessionData.value.qrCodeLoader = false
      platformSessionData.value.qrCode = null
      clearLoadingBarIntervalId()
    }
  }, stopTimeout)
}

const loadingBarProgress = computed(() => {
  return `${loadingProgress.value}%`
})

const stopQrCodeInterval = () => {
  if (modalOpenTimeout) {
    clearTimeout(modalOpenTimeout)
    modalOpenTimeout = null
  }
}

// Handle component lifecycle
onMounted(() => {
  if (props.platform?.status === 'connected') {
    startStatusPolling()
  } else {
    loadQrCode()
  }
  startQrCodeInterval()
})

onUnmounted(() => {
  stopQrCodeInterval()
  clearInterval(statusIntervalId)
  clearLoadingBarIntervalId()
})

// Watch for modal state changes
watch(
  () => modalStore.getState('qrCodeModal'),
  (isOpen) => {
    if (isOpen) {
      shouldRequestStop.value = true
      stopQrCodeInterval()
    } else {
      startQrCodeInterval()
    }
  }
)

// Device management functions
const platformLogout = () => {
  modal.initCallback(() => {
    axios
      .delete(route('user.whatsapp-web.api.platforms.destroy', props.platform.uuid))
      .then((res) => {
        if (res.data?.status === true) {
          toast.success('Session revoked successfully')
          isDeviceConnected.value = false
          shouldRequestStop.value = false
          clearLoadingBarIntervalId()
          loadQrCode()
          startQrCodeInterval()
        }
      })
  })
}

const refreshQrCode = () => {
  isDeviceConnected.value = false
  shouldRequestStop.value = true
  clearLoadingBarIntervalId()
  clearInterval(statusIntervalId)
  loadQrCode()
  stopQrCodeInterval()
  startQrCodeInterval()
  modalStore.close()
}

const switchAuthType = () => {
  platformSessionData.value.authType =
    platformSessionData.value.authType === 'qr_code' ? 'code' : 'qr_code'

  if (platformSessionData.value.authType === 'qr_code') {
    clearLoadingBarIntervalId()
    platformSessionData.value.code = null
    refreshQrCode()
  }

  if (platformSessionData.value.authType === 'code') {
    platformSessionData.value.qrCode = null
    loadCode()
  }
}

const getThemeMode = () => {
  return (
    localStorage.getItem('theme') === 'dark' ||
    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
  )
}
</script>

<template>
  <div class="grid grid-cols-1 place-items-start gap-6 lg:grid-cols-12">
    <!-- Left Panel - How to scan -->
    <div class="card card-body shadow-lg transition-all hover:shadow-xl lg:col-span-4">
      <h2 class="mb-4 text-xl font-bold text-primary-600">{{ trans('How to Scan QR Code') }}</h2>
      <div class="relative mb-6 overflow-hidden rounded-lg">
        <img
          src="/assets/images/scan-demo.gif"
          alt="scan-demo.gif"
          class="w-full rounded-lg shadow-md transition-transform hover:scale-[1.02]"
        />
      </div>

      <div class="relative">
        <!-- Timeline line -->
        <div
          class="absolute left-6 top-6 hidden h-[calc(100%-12px)] w-0.5 bg-gradient-to-b from-primary-500 to-primary-400 shadow-md md:block"
        ></div>

        <!-- Steps -->
        <div class="relative z-10 flex flex-col">
          <div
            v-for="(step, index) in steps"
            :key="index"
            class="relative flex h-24 items-start p-1 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/30"
          >
            <!-- Step indicator -->
            <div class="mr-4 shrink-0">
              <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary-600 to-primary-500 text-white shadow-md transition-all hover:shadow-lg"
              >
                <Icon :icon="step.icon" class="text-xl" />
              </div>
            </div>

            <!-- Step content -->
            <div class="pt-2">
              <p class="mb-1 text-xs font-bold text-primary-500">
                {{ trans('STEP') }} {{ index + 1 }}
              </p>
              <span class="text-sm font-medium">{{ step.text }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Panel - QR Code / Code Entry / Connected State -->
    <div class="card card-body shadow-lg transition-all hover:shadow-xl lg:col-span-8">
      <!-- NOT CONNECTED STATE -->
      <template v-if="!isDeviceConnected">
        <!-- Title -->
        <h2 class="mb-6 text-center text-xl font-bold text-primary-600">
          {{
            trans(
              `${
                platformSessionData.authType === 'qr_code' ? 'Scan QR Code' : 'Enter Code'
              } On Your WhatsApp Mobile App`
            )
          }}
        </h2>

        <!-- QR CODE SECTION -->
        <div
          class="flex flex-col items-center justify-center"
          v-if="platformSessionData.authType === 'qr_code'"
        >
          <!-- Loading spinner -->
          <HollowDotsSpinner v-if="platformSessionData.qrCodeLoader" class="mt-8 scale-150" />

          <!-- QR code container -->
          <div
            class="relative my-8 h-[200px] w-[200px] overflow-hidden rounded-lg shadow-lg transition-all hover:shadow-xl"
            v-if="!platformSessionData.qrCodeLoader && platformSessionData.qrCode"
          >
            <img :src="platformSessionData?.qrCode?.qr" alt="qr-code" class="h-full w-full" />
            <div
              class="absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-lg"
            >
              <Icon icon="bx:bxl-whatsapp" class="text-3xl text-green-500" />
            </div>
          </div>

          <!-- No data or error states -->
          <NoDataFound
            v-else-if="
              !platformSessionData.qrCodeLoader && !platformSessionData.qrCode && !errorCode
            "
          />
          <ErrorWarning v-if="errorCode" :error-code="errorCode" />
        </div>

        <!-- Progress bar - only show when QR code is visible -->
        <div
          v-if="
            !platformSessionData.qrCodeLoader &&
            platformSessionData.qrCode &&
            platformSessionData.authType === 'qr_code'
          "
          class="mx-auto w-4/5 max-w-xs p-1 shadow-inner"
        >
          <div
            class="h-2 rounded-full bg-gradient-to-r from-primary-600 to-primary-400 transition-all duration-300"
            :style="{
              width: loadingBarProgress
            }"
          ></div>
          <p class="mt-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ trans('QR Code expires in') }}
            {{ Math.ceil(loadingProgress * 1.2) }}
            {{ trans('seconds') }}
          </p>
        </div>

        <!-- AUTH TYPE SWITCHER -->
        <div class="mt-8 flex flex-col items-center justify-center gap-2 pb-6">
          <p class="my-4 text-sm text-gray-500" v-if="platformSessionData.authType === 'qr_code'">
            {{ trans('Having trouble scanning?') }}
          </p>

          <div class="flex flex-wrap items-center justify-center gap-3">
            <button
              class="btn flex items-center gap-2 bg-gradient-to-r from-primary-600 to-primary-500 text-white shadow-md transition-all hover:shadow-lg"
              type="button"
              @click="switchAuthType"
            >
              <Icon
                :icon="
                  platformSessionData.authType === 'qr_code'
                    ? 'bx:mobile'
                    : 'material-symbols-light:qr-code-2-rounded'
                "
                class="text-lg"
              />
              {{
                trans(
                  `Connect with ${
                    platformSessionData.authType === 'qr_code' ? 'phone number' : 'QR code'
                  } instead`
                )
              }}
            </button>

            <button
              v-if="platformSessionData.authType === 'qr_code'"
              class="btn flex items-center gap-2 bg-blue-500 text-white shadow-md transition-all hover:shadow-lg"
              @click="refreshQrCode"
              type="button"
            >
              <Icon icon="bx:refresh" class="text-lg" />
              {{ trans('Refresh QR Code') }}
            </button>

            <button
              v-if="platformSessionData.authType === 'code'"
              class="btn flex items-center gap-2 bg-blue-500 text-white shadow-md transition-all hover:shadow-lg"
              @click="loadCode"
              type="button"
            >
              <Icon icon="bx:refresh" class="text-lg" />
              {{ trans('Generate New Code') }}
            </button>
          </div>

          <!-- CODE ENTRY SECTION -->
          <HollowDotsSpinner v-if="platformSessionData.codeLoader" class="mt-6 scale-150" />

          <ErrorWarning
            v-if="
              errorCode &&
              platformSessionData.authType === 'code' &&
              !platformSessionData.qrCodeLoader &&
              !platformSessionData.codeLoader
            "
            :error-code="errorCode"
          />

          <div
            v-if="platformSessionData.code && !platformSessionData.codeLoader"
            class="mt-6 flex flex-col items-center"
          >
            <div class="mb-4 flex justify-center">
              <div
                class="rounded-lg border border-primary-500 bg-white px-8 py-2 shadow-lg dark:bg-gray-800"
              >
                <p class="text-center text-2xl font-bold tracking-widest">
                  {{ platformSessionData.code }}
                </p>
              </div>
            </div>

            <div
              class="mt-2 max-w-sm rounded-lg bg-yellow-50 p-4 text-center dark:bg-yellow-900/30"
            >
              <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                {{ trans('Enter this code on your WhatsApp') }}
              </p>
              <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                {{ trans("Never enter a code if you didn't request it.") }}
              </p>
            </div>
          </div>
        </div>
      </template>

      <!-- CONNECTED STATE -->
      <template v-else>
        <div class="flex flex-col items-center">
          <div class="relative my-6">
            <img
              class="w-60"
              :src="`/assets/images/${getThemeMode() ? '' : 'dark-'}confetti.gif`"
              alt="confetti"
            />
          </div>

          <h2 class="mb-4 text-2xl font-bold text-green-600 dark:text-green-400">
            {{ trans('Connected Successfully') }}
          </h2>

          <p class="mb-8 text-center text-gray-600 dark:text-gray-300">
            {{ trans('Your WhatsApp account is now linked. You can start using the services.') }}
          </p>

          <div class="grid w-full grid-cols-2 gap-4">
            <Link
              class="btn flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg"
              :href="route('user.whatsapp-web.platforms.index')"
            >
              <Icon icon="bx:list-ul" class="text-lg" />
              <span>{{ trans('Chat List') }}</span>
            </Link>

            <Link
              class="btn flex items-center justify-center gap-2 bg-gradient-to-r from-green-600 to-green-500 text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg"
              :href="route('user.whatsapp-web.groups.index')"
            >
              <Icon icon="bx:group" class="text-lg" />
              <span>{{ trans('Group List') }}</span>
            </Link>

            <Link
              class="btn flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-500 text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg"
              :href="route('user.whatsapp-web.send-message.create')"
            >
              <Icon icon="bx:send" class="text-lg" />
              <span>{{ trans('Send Message') }}</span>
            </Link>

            <Link
              class="btn flex items-center justify-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg"
              :href="route('user.whatsapp-web.send-bulk-message.index')"
            >
              <Icon icon="bx:rocket" class="text-lg" />
              <span>{{ trans('Bulk Message') }}</span>
            </Link>
          </div>

          <button
            class="btn mt-6 flex w-full items-center justify-center gap-2 bg-gradient-to-r from-red-600 to-red-500 text-white shadow-md transition-all hover:shadow-lg"
            @click="platformLogout"
          >
            <Icon icon="bx:log-out" class="text-lg" />
            <span>{{ trans('Disconnect Device') }}</span>
          </button>
        </div>
      </template>
    </div>
  </div>

  <!-- Expired QR Code Modal -->
  <Modal state="qrCodeModal">
    <div class="flex flex-col items-center p-6 text-center">
      <div class="mb-4 rounded-full bg-yellow-100 p-4 dark:bg-yellow-900/30">
        <Icon icon="bi:info-circle" class="h-16 w-16 text-yellow-500" />
      </div>

      <h2 class="mb-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
        {{ trans('QR Code Expired') }}
      </h2>

      <p class="mb-6 text-gray-600 dark:text-gray-300">
        {{ trans('The QR code has expired. Please refresh to generate a new one.') }}
      </p>

      <div class="flex gap-3">
        <button
          class="btn flex items-center gap-2 bg-primary-600 text-white shadow-md transition-all hover:bg-primary-700"
          type="button"
          @click="refreshQrCode"
        >
          <Icon icon="bx:refresh" class="text-lg" />
          {{ trans('Generate New QR') }}
        </button>

        <button
          class="btn flex items-center gap-2 bg-gray-500 text-white shadow-md transition-all hover:bg-gray-600"
          @click="modalStore.close"
        >
          <Icon icon="bx:x" class="text-lg" />
          {{ trans('Close') }}
        </button>
      </div>
    </div>
  </Modal>
</template>
