<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import { ref } from 'vue'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })
const { formatCurrency, textExcerpt } = sharedComposable()

const props = defineProps([
  'creditLogs',
  'per_credit_fee',
  'totalCreditLog',
  'activeCreditLog',
  'inactiveCreditLog',
  'request',
  'type'
])
const filterData = useForm({
  search: props.request.search,
  type: props.type
})

const creditFeeForm = useForm({
  per_credit_fee: props.per_credit_fee
})

const creditFeeUpdate = () => {
  creditFeeForm.put(route('admin.update-credit-fee'), {
    onSuccess: () => modalStore.close('updateCreditFeeModal')
  })
}

const editCreditLogForm = ref({})
const openEditCreditModal = (creditLog) => {
  editCreditLogForm.value = { ...creditLog }
  modalStore.open('editCreditModal')
}
const updateCreditLog = () => {
  router.patch(
    route('admin.credit-logs.update', editCreditLogForm.value.id),
    editCreditLogForm.value,
    {
      onSuccess: () => modalStore.open('editCreditModal')
    }
  )
}
</script>

<template>
  <div class="space-y-2">
    <div class="dropdown" data-placement="bottom-end">
      <div class="dropdown-toggle">
        <button type="button" class="btn bg-white font-medium shadow-sm dark:bg-slate-800">
          <Icon icon="fe:filter" />
          <span>{{ trans('Filter') }}</span>
          <Icon icon="bx:chevron-down" />
        </button>
      </div>

      <div class="dropdown-content w-72 !overflow-visible">
        <form @submit.prevent="filterData.get('/admin/credit-logs')">
          <ul class="dropdown-list space-y-4 p-4">
            <li class="dropdown-list-item">
              <h2 class="my-1 text-sm font-medium">{{ trans('Status') }}</h2>
              <div class="mb-2">
                <input
                  type="text"
                  name="search"
                  v-model="filterData.search"
                  class="input"
                  placeholder="Search......"
                />
              </div>
            </li>
            <li class="dropdown-list-item">
              <div class="mb-2">
                <select class="select" name="type" v-model="filterData.type">
                  <option value="email">{{ trans('User Email') }}</option>
                  <option value="invoice_no">{{ trans('Invoice No') }}</option>
                </select>
              </div>
            </li>

            <li class="dropdown-list-item">
              <button type="submit" class="btn btn-primary w-full">
                {{ trans('Filter') }}
              </button>
            </li>
          </ul>
        </form>
      </div>
    </div>
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Invoice') }}</th>
            <th>{{ trans('User Name') }}</th>
            <th>{{ trans('Credits') }}</th>

            <th>
              {{ trans('Price') }}
            </th>
            <th>
              {{ trans('Status') }}
            </th>
            <th>
              {{ trans('Gateway') }}
            </th>
            <th>
              {{ trans('Comment') }}
            </th>
            <th>
              {{ trans('Attachment') }}
            </th>
            <th>{{ trans('Date') }}</th>
            <th class="flex justify-end">{{ trans('Action') }}</th>
          </tr>
        </thead>

        <tbody v-if="creditLogs.total > 0">
          <tr v-for="creditLog in creditLogs.data" :key="creditLog.id">
            <td>
              <p class="text-primary-500">{{ creditLog.invoice_no }}</p>
            </td>
            <td>
              <Link :href="route('admin.users.show', creditLog.user_id)">
                {{ textExcerpt(creditLog.user.name, 30) }}
              </Link>
            </td>
            <td>
              {{ creditLog.credits }}
            </td>
            <td>
              {{ formatCurrency(creditLog.price) }}
            </td>
            <td>
              <span :class="creditLog.status == 1 ? 'badge badge-success' : 'badge badge-warning'">
                {{ creditLog.status == 1 ? 'Complete' : 'Pending' }}
              </span>
            </td>
            <td>
              {{ creditLog.gateway.name }}
            </td>
            <td>
              <span v-if="creditLog?.meta">
                {{ JSON.parse(creditLog.meta).comment }}
              </span>
              <span v-else>{{ trans('None') }}</span>
            </td>
            <td>
              <template v-if="creditLog?.meta">
                <a :href="sanitizeUrl(JSON.parse(creditLog.meta).screenshot)" target="_blank">{{
                  trans('View Attachment')
                }}</a>
              </template>
              <span v-else>{{ trans('None') }}</span>
            </td>
            <td class="text-left">
              {{ moment(creditLog.updated_at).format('DD MMM, YYYY') }}
            </td>
            <td>
              <div class="flex justify-end">
                <div class="dropdown" data-placement="bottom-start">
                  <div class="dropdown-toggle">
                    <Icon class="text-2xl" icon="bx:dots-horizontal-rounded" />
                  </div>
                  <div class="dropdown-content w-40">
                    <ul class="dropdown-list">
                      <li class="dropdown-list-item">
                        <button @click="openEditCreditModal(creditLog)" class="dropdown-link">
                          <Icon icon="fe:edit" />
                          <span>{{ trans('Edit') }}</span>
                        </button>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else for-table="true" />
      </table>
    </div>
    <Paginate :links="creditLogs.links" />
  </div>

  <Modal
    state="updateCreditFeeModal"
    :header-state="true"
    header-title="Update Credit Fee"
    :action-btn-state="true"
    :action-btn-text="trans('Save Changes')"
    :action-processing="creditFeeForm.processing"
    @action="creditFeeUpdate"
  >
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Per Credit Fee') }}</label>
      <input
        v-model="creditFeeForm.per_credit_fee"
        type="number"
        step="any"
        required
        class="input"
      />
    </div>
  </Modal>

  <Modal
    state="editCreditModal"
    :header-state="true"
    header-title="Edit Credit Fee"
    :action-btn-state="true"
    :action-btn-text="trans('Save Changes')"
    :action-processing="editCreditLogForm.processing"
    @action="updateCreditLog"
  >
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Status') }}</label>
      <select v-model="editCreditLogForm.status" class="select" name="status">
        <option value="1">
          {{ trans('Approved') }}
        </option>
        <option value="0">
          {{ trans('Pending') }}
        </option>
      </select>
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Credits') }}</label>
      <input
        v-model="editCreditLogForm.credits"
        type="number"
        maxlength="500"
        class="input"
        required
      />
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Payment ID') }}</label>
      <input
        :value="editCreditLogForm.payment_id"
        type="text"
        class="input read-only:cursor-not-allowed"
        readonly
      />
    </div>
  </Modal>
</template>
