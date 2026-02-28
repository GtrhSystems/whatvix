<script setup>
import { computed, onMounted, ref } from 'vue'

import axios from 'axios'
import ShortCodes from '@/Components/Forms/ShortCodes.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import OverviewGrid from '@/Components/Dashboard/OverviewGrid.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import RangeSlider from '@/Components/RangeSlider.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import toast from '@/Composables/toastComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'

import MultiSelect from '@/Components/Forms/MultiSelect.vue'

const props = defineProps(['platforms', 'templates', 'groups'])
defineOptions({ layout: UserLayout })
const { deleteRow, badgeClass } = sharedComposable()
const modalStore = useModalStore()
onMounted(() => {
  modalStore.open('configBulkSend')
})
const overviews = computed(() => {
  return [
    {
      title: 'Total Messages',
      value: messageSendingStarted.value ? form.value.contactIndex + 1 : 0,
      icon: 'bx:message'
    },
    {
      title: 'Failed Messages',
      value: contactList.value?.filter((contact) => contact.status === 'failed').length || 0,
      icon: 'bx:x-circle'
    },
    {
      title: 'Pending Messages',
      value: contactList.value?.filter((contact) => contact.status === 'pending').length || 0,
      icon: 'bx:user'
    },
    {
      title: 'Sent Messages',
      value: contactList.value?.filter((contact) => contact.status === 'sent').length || 0,
      icon: 'bx:check-circle'
    }
  ]
})
const form = ref({
  group_ids: [],
  platforms_ids: [],
  message_type: 'text',
  message_delay: [1, 60],
  loading: false,
  contactIndex: 0
})
const contactForm = ref({
  contacts: ''
})
const messageSending = ref(false)
const messageSendingStarted = ref(false)
const messageSendingFinished = ref(false)
const contactList = ref([])
const newContacts = ref([])

const getContactList = () => {
  if (messageSendingStarted.value) {
    return toast.danger('Cannot configure after sending messages')
  }
  if (form.value.group_ids.length === 0) {
    return toast.danger('No Groups Selected')
  }
  if (!form.value.message && !form.value.template_id) {
    return toast.danger('No Message or Template Selected')
  }
  axios
    .get(
      route('user.whatsapp-web.api.bulk-send.contact_list', {
        group_ids: form.value.group_ids
      })
    )
    .then((res) => {
      contactList.value = res.data
      if (newContacts.value.length > 0) {
        contactList.value = [...contactList.value, ...newContacts.value]
      }
    })
}

const sendMessage = () => {
  if (contactList.value.length === 0) {
    return toast.danger('No Contacts Found')
  }
  if (!form.value.message && form.value.message_type === 'text') {
    return toast.danger('No Message added')
  }
  if (!form.value.template_id && form.value.message_type === 'template') {
    return toast.danger('No Template Selected')
  }
  if (form.value.platforms_ids.length === 0) {
    return toast.danger('No Devices Selected')
  }
  messageSending.value = true
  messageSendingStarted.value = true
  form.value.loading = true
  dispatchMessage()
}
const replaceShortCodes = (message, data) => {
  return message.replace(/{name}/g, data)
}
const dispatchMessage = () => {
  if (!messageSending.value) return
  setTimeout(() => {}, 100000)
  const randomDevice = Math.floor(Math.random() * form.value.platforms_ids.length)

  const currentContact = contactList.value[form.value.contactIndex]
  let newMessage
  if (form.value.message_type === 'text') {
    newMessage = replaceShortCodes(form.value.message, currentContact?.name)
  }
  axios
    .post(route('user.whatsapp-web.api.bulk-send.message'), {
      form: {
        message_type: form.value.message_type,
        message: newMessage || null,
        template_id: form.value?.template_id || null,
        platform_id: form.value.platforms_ids[randomDevice]
      },
      contact: currentContact
    })
    .then((res) => {
      contactList.value = contactList.value.map((contact, index) => {
        if (index === form.value.contactIndex) {
          return { ...contact, status: 'sent' }
        }
        return contact
      })
    })
    .catch((err) => {
      contactList.value = contactList.value.map((contact, index) => {
        if (index === form.value.contactIndex) {
          return { ...contact, status: 'failed' }
        }
        return contact
      })
      toast.danger('Something went wrong!')
    })
    .finally(() => {
      let hasNextMessage = form.value.contactIndex < contactList.value.length - 1
      hasNextMessage ? sendNextMessage() : finishedMessageSend()
    })
}

const sendNextMessage = () => {
  if (messageSending.value) {
    console.log('sending next message')
    form.value.contactIndex++
    const delay =
      Math.floor(Math.random() * form.value.message_delay[1]) + form.value.message_delay[0]
    setTimeout(() => dispatchMessage(), delay * 1000)
  }
}

const finishedMessageSend = () => {
  form.value.contactIndex = 0
  messageSending.value = false
  form.value.loading = false
  messageSendingFinished.value = true
}

const stopSending = () => {
  messageSending.value = false
  form.value.loading = false
}

const continueSending = () => {
  if (form.value.contactIndex < contactList.value.length) {
    messageSending.value = true
    form.value.loading = true
    dispatchMessage()
  } else {
    toast.info('All messages have been sent')
  }
}

const addContact = () => {
  if (messageSendingStarted.value) {
    return toast.danger('Cannot add contacts after sending messages')
  }
  const contacts = contactForm.value.contacts.split(',')

  if (contacts.length > 0) {
    const newlyAddedContacts = contacts.map((contact) => {
      const [name, phone] = contact.includes('+') ? contact.split('+') : ['guest', contact]
      return {
        name: name || 'guest',
        phone: phone,
        status: 'pending'
      }
    })
    newContacts.value = [...newContacts.value, ...newlyAddedContacts]
    contactList.value = [...contactList.value, ...newlyAddedContacts]
    contactForm.value.contacts = ''
    modalStore.close('addContact')
  }
}
const removeContact = (index) => {
  contactList.value.splice(index, 1)
}
const findTemplate = computed(() => {
  if (form.value.message_type === 'template' && form.value?.template_id) {
    return props.templates.find((template) => template.id === form.value.template_id)
  }
  return null
})
</script>

<template>
  <OverviewGrid :items="overviews" :grid="4" />
  <div class="flex justify-end gap-2" v-if="!messageSendingFinished">
    <SpinnerBtn
      @click="stopSending"
      v-if="form.loading"
      btn-text="Stop Sending"
      classes="btn btn-danger"
      icon="bx:stop"
    />
    <SpinnerBtn
      v-if="!messageSendingStarted"
      @click="sendMessage"
      :processing="form.loading"
      btn-text="Send Now"
      icon="bx:rocket"
    />
    <SpinnerBtn
      v-if="messageSendingStarted"
      @click="continueSending"
      :processing="form.loading"
      :btn-text="trans(messageSending ? 'Sending...' : 'Continue Sending')"
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
          <th>
            {{ trans('Message/Template') }}
          </th>

          <th>
            {{ trans('Status') }}
          </th>
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
            {{ contact?.group_name || trans('None') }}
          </td>
          <td>
            {{ contact.phone }}
          </td>
          <td>
            <span v-if="form.message_type === 'template'">
              {{ findTemplate?.name || trans('None') }}
            </span>
            <span v-else-if="form.message_type === 'text'">
              {{ form?.message ? replaceShortCodes(form.message, contact.name) : trans('None') }}
            </span>
            <span v-else>{{ trans('None') }}</span>
          </td>

          <td>
            <span :class="badgeClass(contact.status)">{{ contact.status }}</span>
          </td>
          <td class="!text-right">
            <button
              :disabled="form.loading || messageSendingStarted"
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
    :backdropClose="false"
    :closeBtn="true"
    :header-state="true"
    header-title="Configure Bulk Send"
    state="configBulkSend"
  >
    <div class="space-y-2">
      <MultiSelect
        label="Select Rotation Devices"
        v-model="form.platforms_ids"
        placeholder="Select Rotation Devices"
        :validationMessage="form.errors?.platforms_ids"
        :options="platforms"
      />
      <div>
        <label for="device_rotation_duration" class="label mb-1.5">
          {{ trans('Message Delay (in seconds)') }}
        </label>
        <RangeSlider class="px-3" :min="1" :max="60" v-model="form.message_delay" :step="1" />
      </div>
      <MultiSelect
        label="Select Groups"
        v-model="form.group_ids"
        placeholder="Select Groups"
        :validationMessage="form.errors?.group_ids"
        :options="groups"
      />
      <div>
        <label class="label mb-1">{{ trans('Message Type') }}</label>
        <select v-model="form.message_type" class="select">
          <option value="template">{{ trans('Template') }}</option>
          <option value="text">{{ trans('Text') }}</option>
        </select>
      </div>
      <template v-if="form.message_type === 'template'">
        <div>
          <label class="label mb-1">{{ trans('Select Template') }}</label>
          <select v-model="form.template_id" class="select">
            <option value="undefined" disabled selected>{{ trans('Select Template') }}</option>
            <option v-for="template in templates" :key="template.id" :value="template.id">
              {{ template.name }}
            </option>
          </select>
        </div>
      </template>
      <template v-if="form.message_type === 'text'">
        <div>
          <label class="label mb-1">{{ trans('Message') }}</label>
          <textarea name="message" v-model="form.message" class="textarea"></textarea>
          <ShortCodes v-model="form.message" />
        </div>
      </template>
      <div class="flex justify-end">
        <SpinnerBtn @click="getContactList" :btn-text="trans('Save')" />
      </div>
    </div>
  </Modal>
  <!-- add contact -->
  <Modal :header-state="true" header-title="Add Contact" state="addContact">
    <div class="space-y-2">
      <div>
        <label class="label mb-1">{{ trans('Contacts') }}</label>

        <textarea
          placeholder="Use comma to separate multiple contacts, e.g. John+1234567890, Jane+0987654321"
          name="contacts"
          v-model="contactForm.contacts"
          class="textarea max-h-40"
        ></textarea>
      </div>

      <div v-if="form.message_type === 'text'">
        <label class="label mb-1">{{ trans('Message') }}</label>
        <textarea
          name="message"
          v-model="form.message"
          class="textarea max-h-40"
          placeholder="Message"
        ></textarea>
        <ShortCodes v-model="form.message" />
      </div>

      <div class="flex justify-end">
        <SpinnerBtn @click="addContact" :btn-text="trans('Add Contact')" />
      </div>
    </div>
  </Modal>
</template>
