<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { useForm } from '@inertiajs/vue3'
import trans from '@/Composables/transComposable'
import Pagination from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import sharedComposable from '@/Composables/sharedComposable'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })

const { formatCurrency } = sharedComposable()
const props = defineProps(['buttons', 'orders', 'request', 'type', 'currency', 'invoice', 'tax'])

const invoiceFrom = useForm({
  company_name: props.invoice.company_name,
  address: props.invoice.address,
  city: props.invoice.city,
  post_code: props.invoice.post_code,
  country: props.invoice.country
})

const taxFrom = useForm({
  tax: props.tax
})

function updateOption(form, key, modalName) {
  form.put(route('admin.option.update', key), {
    onSuccess: () => modalStore.close(modalName)
  })
}

const currencyFrom = useForm({
  name: props.currency.name,
  icon: props.currency.icon,
  position: props.currency.position
})

const filterOptions = [
  {
    value: 'invoice_no',
    label: 'Order no'
  },
  {
    value: 'user_email',
    label: 'User Email'
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Approved',
        value: 'approved'
      },
      {
        label: 'Pending',
        value: 'pending'
      },
      {
        label: 'Rejected',
        value: 'rejected'
      }
    ]
  }
]
</script>

<template>
  <!-- Main Content Starts -->

  <div class="mt-4 space-y-4">
    <FilterDropdown :options="filterOptions" />
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Order No') }}</th>
            <th>{{ trans('Plan Name') }}</th>
            <th>{{ trans('Payment Mode') }}</th>
            <th>{{ trans('Amount') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Created At') }}</th>
            <th class="!text-right">
              {{ trans('Actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="orders.total">
          <tr v-for="order in orders.data" :key="order.id">
            <td>
              <Link
                :href="'/admin/order/' + order.id"
                class="text-sm font-medium text-primary-500 transition duration-150 ease-in-out hover:underline"
              >
                {{ order.invoice_no }}
              </Link>
            </td>
            <td>{{ order.plan.title }}</td>
            <td>{{ order.gateway.name }}</td>
            <td>{{ formatCurrency(order.amount) }}</td>
            <td>
              <div class="badge badge-soft-primary capitalize">
                {{ order.status }}
              </div>
            </td>
            <td class="text-center">
              {{ order.created_at_diff }}
            </td>
            <td>
              <div class="flex justify-end">
                <div class="dropdown" data-placement="bottom-start">
                  <div class="dropdown-toggle">
                    <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                  </div>
                  <div class="dropdown-content w-40">
                    <ul class="dropdown-list">
                      <li class="dropdown-list-item">
                        <Link :href="'/admin/order/' + order.id" class="dropdown-link">
                          <i class="h-5 text-slate-400" data-feather="external-link"></i>
                          <span>{{ trans('View') }}</span>
                        </Link>
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

    <Pagination v-if="orders.data.length != 0" :links="orders.links" />
  </div>
  <!-- Main Content Ends -->

  <Modal
    state="invoiceSettingModal"
    :header-state="true"
    header-title="Edit Invoice Information"
    :action-btn-state="true"
    action-btn-text="Submit"
    :action-processing="invoiceFrom.processing"
    @action="updateOption(invoiceFrom, 'invoice_data', 'invoiceSettingModal')"
  >
    <div class="mb-2">
      <label>{{ trans('Company Name') }}</label>
      <input type="text" v-model="invoiceFrom.company_name" class="input" required="" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Company Address') }}</label>
      <input
        type="text"
        name="data[address]"
        v-model="invoiceFrom.address"
        class="input"
        required=""
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Company City') }}</label>
      <input type="text" name="data[city]" v-model="invoiceFrom.city" class="input" required="" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Post Code') }}</label>
      <input
        type="text"
        name="data[post_code]"
        v-model="invoiceFrom.post_code"
        class="input"
        required=""
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Country') }}</label>
      <input
        type="text"
        name="data[country]"
        v-model="invoiceFrom.country"
        class="input"
        required=""
      />
    </div>
  </Modal>

  <Modal
    state="taxSettingModal"
    :header-state="true"
    header-title="Tax Settings"
    :action-btn-state="true"
    action-btn-text="Submit"
    :action-processing="taxFrom.processing"
    @action="updateOption(taxFrom, 'tax', 'taxSettingModal')"
  >
    <div class="mb-2">
      <label>{{ trans('Tax Amount') }}</label>
      <input type="number" step="any" name="data" v-model="taxFrom.tax" class="input" required />
    </div>
  </Modal>

  <Modal
    state="currencySettingModal"
    :header-state="true"
    header-title="Currency Settings"
    :action-btn-state="true"
    action-btn-text="Submit"
    :action-processing="currencyFrom.processing"
    @action="updateOption(currencyFrom, 'base_currency', 'currencySettingModal')"
  >
    <div class="mb-2">
      <label>{{ trans('Currency Name') }}</label>
      <input type="text" name="data[name]" v-model="currencyFrom.name" class="input" required="" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Currency Icon') }}</label>
      <input type="text" name="data[icon]" v-model="currencyFrom.icon" class="input" required="" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Currency Icon') }}</label>
      <select class="select" name="data[position]" v-model="currencyFrom.position">
        <option value="left">
          {{ trans('Left') }}
        </option>
        <option value="right">
          {{ trans('Right') }}
        </option>
      </select>
    </div>
  </Modal>
</template>
