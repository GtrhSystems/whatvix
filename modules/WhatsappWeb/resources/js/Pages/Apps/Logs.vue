<script setup>
import { ref } from 'vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'

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
  <Modal :header-state="true" :header-title="'Log Details - ' + selectLogType" state="modal">
    <pre class="card card-body">
            {{ selectedLog[selectLogType] }}
        </pre
    >
  </Modal>

  <div class="card card-body mb-2">
    <p>
      <strong>{{ trans('Name') }}</strong> {{ app.name }}
    </p>
    <p>
      <strong>{{ trans('App Key') }}</strong> {{ app.key }}
    </p>
    <p>
      <strong>{{ trans('App Link') }}</strong> {{ app.site_link }}
    </p>
    <p>
      <strong>{{ trans('Created') }}</strong> {{ app.created_at }}
    </p>
  </div>
  <div class="table-responsive w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Platform') }}</th>
          <th>{{ trans('From') }}</th>
          <th>{{ trans('To') }}</th>
          <th>{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Created At') }}
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
            {{ log.platform.meta?.phone_number }}
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
            {{ moment(log.created_at).toLocaleString() }}
          </td>
          <td class="!text-right">
            <button
              class="btn btn-outline-primary btn-xs mr-2"
              @click="openModalFor(log, 'request')"
            >
              {{ trans('Request') }}
            </button>
            <button class="btn btn-outline-primary btn-xs" @click="openModalFor(log, 'response')">
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
