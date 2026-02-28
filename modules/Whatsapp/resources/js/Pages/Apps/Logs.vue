<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import sharedComposable from '@/Composables/sharedComposable'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import JsonHighlighter from '@whatsapp/Components/JsonHighlighter.vue'
import FloatingDropdown from '@/Components/Dashboard/FloatingDropdown.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import moment from 'moment'

const modalStore = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['app', 'logs'])

const selectLogType = ref('request')
const selectedLog = ref(null)

const openModalFor = (log, type) => {
  selectedLog.value = log
  selectLogType.value = type
  modalStore.open('modal', selectLogType.value)
}
</script>

<template>
  <Modal
    modalSize="w-3/5"
    :header-state="true"
    :header-title="'Log Details - ' + selectLogType"
    state="modal"
  >
    <JsonHighlighter :code="selectedLog[selectLogType]" />
  </Modal>

  <div class="table-responsive w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Platform') }}</th>
          <th>{{ trans('To') }}</th>
          <th>{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Data Time') }}
          </th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="logs.data.length" class="tbody">
        <tr v-for="(log, index) in logs.data" :key="index">
          <td>
            {{ log.platform?.name }}
          </td>
          <td>
            {{ log.to }}
          </td>
          <td>
            <span
              v-if="log.status_code"
              class="badge"
              :class="log.status_code == 200 ? 'badge-success' : 'badge-danger'"
            >
              {{ log.status_code }}
            </span>
          </td>

          <td class="!text-right">
            {{ moment(log.created_at).format('DD/MM/YYYY h:m a') }}
          </td>
          <td class="!text-right">
            <button
              class="btn btn-outline-primary btn-xs mr-2"
              @click="openModalFor(log, 'request')"
            >
              {{ trans('Request') }}
            </button>
            <button class="btn btn-outline-info btn-xs" @click="openModalFor(log, 'response')">
              {{ trans('Response') }}
            </button>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>

    <Paginate :links="logs.links" />
  </div>
</template>
