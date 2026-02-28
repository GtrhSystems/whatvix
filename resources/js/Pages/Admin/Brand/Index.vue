<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import { ref } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()

const props = defineProps(['brands'])

const form = useForm({
  url: '',
  status: '1',
  type: 'partner',
  preview: null
})

const isProcessing = ref(false)
const editPartnerForm = ref({})

const storePartner = () => {
  form.post(route('admin.partner.store'), {
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}

function openEditFaqDrawer(partner) {
  editPartnerForm.value = { ...partner, _method: 'patch' }
  modalStore.open('editModal')
}

const updatePartner = () => {
  if (!editPartnerForm.value.preview instanceof File) {
    editPartnerForm.value.preview = null
  }

  isProcessing.value = true
  router.post(route('admin.partner.update', editPartnerForm.value.id), editPartnerForm.value, {
    onSuccess: () => {
      editPartnerForm.value = {}
      isProcessing.value = false
      modalStore.close('editModal')
    }
  })
}
</script>

<template>
  <div class="card">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Image') }}</th>
            <th>{{ trans('Url') }}</th>
            <th>{{ trans('Type') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Created At') }}</th>
            <th class="!text-right">{{ trans('Action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in brands.data" :key="row.id">
            <td class="text-left">
              <div class="w-28 rounded-md bg-gray-400 px-1 dark:bg-gray-600">
                <img :src="row.preview" alt="preview" class="h-full w-full" />
              </div>
            </td>
            <td class="text-left">
              {{ row.title }}
            </td>
            <td class="text-left">
              {{ row.lang == 'en' ? 'Partner' : row.lang }}
            </td>

            <td class="text-left">
              <span class="badge" :class="row.status == 1 ? 'badge-success' : 'badge-danger'">
                {{ row.status == 1 ? trans('Active') : trans('Draft') }}
              </span>
            </td>
            <td>
              {{ moment(row.created_at).format('DD MMM, YYYY') }}
            </td>

            <td>
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="text-2xl" icon="bx:dots-horizontal-rounded" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <button @click="openEditFaqDrawer(row)" class="dropdown-link">
                        <Icon icon="fe:edit" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link"
                        @click="deleteRow(route('admin.partner.destroy', row.id))"
                      >
                        <Icon icon="fe:trash" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-if="!brands.total" :for-table="true" />
      </table>
    </div>
  </div>
  <Paginate :links="brands.links" />

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Create Partner')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="form.processing"
    @action="storePartner"
  >
    <div class="mb-2">
      <label>{{ trans('Brand Url') }}</label>
      <input type="text" name="url" v-model="form.url" class="input" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Brand image') }}</label>
      <input
        @input="(e) => (form.preview = e.target.files[0])"
        type="file"
        accept="image/*"
        name="image"
        required
        class="input"
      />
    </div>

    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select class="select" name="status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Deactivate') }}</option>
      </select>
    </div>
  </Modal>

  <Modal
    state="editModal"
    :header-state="true"
    :header-title="trans('Edit Partner')"
    :action-btn-text="trans('Update')"
    :action-btn-state="true"
    :action-processing="form.processing"
    @action="updatePartner"
  >
    <div class="mb-2">
      <label>{{ trans('Brand Url') }}</label>
      <input type="text" name="url" v-model="editPartnerForm.title" class="input" id="url" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Brand image') }}</label>
      <input
        @input="(e) => (editPartnerForm.preview = e.target.files[0])"
        type="file"
        name="image"
        accept="image/*"
        class="input"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="editPartnerForm.status" class="select" name="status" id="status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Deactivate') }}</option>
      </select>
    </div>
  </Modal>
</template>
