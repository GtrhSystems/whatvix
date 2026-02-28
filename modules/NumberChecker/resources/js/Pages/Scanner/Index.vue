<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import Modal from '@/Components/Dashboard/Modal.vue'
import OverviewGrid from '@/Components/Dashboard/OverviewGrid.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import toast from '@/Composables/toastComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import { router } from '@inertiajs/vue3'

defineOptions({ layout: UserLayout })

const props = defineProps(['devices', 'groups', 'number_scanned'])

const { deleteRow, badgeClass } = sharedComposable()
const modalStore = useModalStore()
onMounted(() => {
  modalStore.open('configBulkSend')
})
const totalNumberScanned = ref(props.number_scanned || 0)
const overviews = computed(() => {
  return [
    {
      title: 'Total Number Scanned',
      value: totalNumberScanned.value,
      icon: 'bx:list-ol'
    },

    {
      title: 'Not Checked',
      value: contactList.value?.filter((contact) => !contact.status).length || 0,
      icon: 'bx:hourglass'
    },
    {
      title: 'Checked Contact',
      value: contactList.value?.filter((contact) => contact.status === 'checked').length || 0,
      icon: 'bx:check-circle'
    },

    {
      title: 'Number Scanned',
      value: totalNumberScanned.value,
      icon: 'bx:qr'
    }
  ]
})
const form = ref({
  group_id: '',
  device_id: '',
  loading: false,
  contactIndex: 0
})

const numberScanning = ref(false)
const scanningStarted = ref(false)
const messageSendingFinished = ref(false)
const contactList = ref([])
const newContacts = ref([])
const isUnverifiedContactRemoved = ref(false)

const getContactList = () => {
  if (scanningStarted.value) {
    return toast.danger('Cannot configure after sending messages')
  }
  if (!form.value.group_id) {
    return toast.danger('No Groups Selected')
  }
  axios
    .get(
      route('user.whatsapp-web.api.bulk-send.contact_list', {
        group_ids: [form.value.group_id]
      })
    )
    .then((res) => {
      contactList.value = res.data
      if (newContacts.value.length > 0) {
        contactList.value = [...contactList.value, ...newContacts.value]
      }
    })
}

const startScanning = () => {
  if (contactList.value.length === 0) {
    return toast.danger('No Contacts Found')
  }
  numberScanning.value = true
  scanningStarted.value = true
  form.value.loading = true
  dispatchScanning()
}

const dispatchScanning = () => {
  if (!numberScanning.value) return
  setTimeout(() => {}, 100000)

  axios
    .post(route('user.number-checker.api.scanner'), {
      platform_id: form.value.device_id,
      contact: contactList.value[form.value.contactIndex]
    })
    .then((res) => {
      contactList.value = contactList.value.map((contact, index) => {
        if (index === form.value.contactIndex) {
          return { ...contact, has_whatsapp: res.data.exists ? 'yes' : 'no', status: 'checked' }
        }
        return contact
      })
      totalNumberScanned.value++
    })
    .catch((err) => {
      toast.danger('Something went wrong!')
    })
    .finally(() => {
      let hasNextMessage = form.value.contactIndex < contactList.value.length - 1
      hasNextMessage ? sendNextScanning() : finishedScanning()
    })
}

const sendNextScanning = () => {
  if (numberScanning.value) {
    form.value.contactIndex++
    setTimeout(() => dispatchScanning(), 1000)
  }
}

const finishedScanning = () => {
  form.value.contactIndex = 0
  numberScanning.value = false
  form.value.loading = false
  messageSendingFinished.value = true
}

const stopScanning = () => {
  numberScanning.value = false
  form.value.loading = false
}

const continueScanning = () => {
  if (form.value.contactIndex < contactList.value.length) {
    numberScanning.value = true
    form.value.loading = true
    dispatchScanning()
  } else {
    toast.info('All numbers have been scanned')
  }
}

const removeContact = (index) => {
  contactList.value.splice(index, 1)
}

const removeAllNoneVerified = () => {
  contactList.value = contactList.value.filter((contact) => contact.has_whatsapp === 'yes')
  isUnverifiedContactRemoved.value = true
}
const submitContactList = () => {
  if (!isUnverifiedContactRemoved.value) {
    return toast.danger('No changes made')
  }
  form.value.loading = true
  router.patch(
    route('user.whatsapp-web.groups.update-customers', form.value.group_id),
    {
      customers: contactList.value.map((contact) => contact.id),
      should_delete: true
    },
    {
      onFinish: () => {
        form.value.loading = false
      }
    }
  )
}
</script>

<template>
  <OverviewGrid :items="overviews" :grid="4" />
  <div class="flex justify-end gap-2" v-if="!messageSendingFinished">
    <SpinnerBtn
      @click="stopScanning"
      v-if="form.loading"
      btn-text="Stop Scanning"
      classes="btn btn-danger"
      icon="bx:stop"
    />
    <SpinnerBtn
      v-if="!scanningStarted"
      @click="startScanning"
      :processing="form.loading"
      btn-text="Start Scanning"
      icon="bx:rocket"
    />
    <SpinnerBtn
      v-if="scanningStarted"
      @click="continueScanning"
      :processing="form.loading"
      :btn-text="numberScanning ? 'Scanning...' : 'Continue Scanning'"
      icon="bx:rocket"
    />
  </div>
  <div class="flex justify-end gap-2" v-if="messageSendingFinished">
    <button
      class="btn btn-danger"
      @click="removeAllNoneVerified"
      :disabled="isUnverifiedContactRemoved"
    >
      <Icon icon="bx:trash" />
      <span>{{ trans('Remove None Verified') }}</span>
    </button>
    <SpinnerBtn
      @click="submitContactList"
      :processing="form.loading"
      btn-text="Submit & Update Contact List"
      icon="bx:send"
    />
  </div>
  <div class="table-responsive mt-3 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Name') }}
          </th>
          <th>{{ trans('Group') }}</th>
          <th>{{ trans('Phone') }}</th>
          <th>{{ trans('Has Whatsapp?') }}</th>
          <th>{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="contactList.length" class="tbody">
        <tr v-for="(contact, index) in contactList" :key="index">
          <td>
            {{ contact.name }}
          </td>
          <td>
            {{ contact?.group_name || 'None' }}
          </td>
          <td>
            {{ contact.phone }}
          </td>

          <td>
            <span :class="badgeClass(contact?.has_whatsapp)">
              {{ contact?.has_whatsapp }}
            </span>
          </td>

          <td>
            <span :class="badgeClass(contact?.status)">
              {{ contact?.status }}
            </span>
          </td>
          <td class="!text-right">
            <button
              :disabled="form.loading"
              class="btn btn-sm btn-danger"
              @click="removeContact(index)"
            >
              <Icon icon="bx:trash" />
            </button>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
  </div>
  <Modal
    :closeBtn="true"
    :header-state="true"
    header-title="Select Device & Group"
    state="configBulkSend"
  >
    <div class="space-y-2">
      <div class="">
        <label class="label mb-1">{{ trans('Select Device') }}</label>
        <select v-model="form.device_id" class="select">
          <option value="">{{ trans('Select Device') }}</option>
          <option v-for="device in devices" :key="device.id" :value="device.id">
            {{ device.name }}
          </option>
        </select>
      </div>
      <div class="">
        <label class="label mb-1">{{ trans('Select Group') }}</label>
        <select v-model="form.group_id" class="select">
          <option value="" selected disabled>{{ trans('Select Group') }}</option>
          <option v-for="group in groups" :key="group.id" :value="group.id">
            {{ group.name }}
          </option>
        </select>
      </div>

      <div class="flex justify-end">
        <SpinnerBtn @click="getContactList" :btn-text="trans('Save')" />
      </div>
    </div>
  </Modal>
</template>
