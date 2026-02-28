<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

import { useForm } from '@inertiajs/vue3'
import Pagination from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import { onMounted } from 'vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'

defineOptions({ layout: AdminLayout })
const { deleteRow, formatCurrency, uiAvatar } = sharedComposable()

const props = defineProps(['users', 'plans'])

const editForm = useForm({
  user_id: null,
  name: '',
  email: '',
  plan_id: '',
  will_expire: '',
  assign_plan: true
})

const openEditUserModal = (user) => {
  editForm.reset()

  editForm.user_id = user.id
  editForm.name = user.name
  editForm.email = user.email
  editForm.plan_id = user.plan_id ?? ''
  editForm.will_expire = user.will_expire ?? ''

  modalStore.open('editUserModal')
}

const updateUser = () => {
  editForm.put(route('admin.users.assign.plan', editForm.user_id), {
    onSuccess: () => {
      editForm.reset()
      modalStore.close('editUserModal')
    }
  })
}

const filterOptions = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Email',
    value: 'email'
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Active',
        value: 1
      },
      {
        label: 'Inactive',
        value: 0
      }
    ]
  }
]
</script>

<template>
  <div class="mt-4 space-y-4">
    <FilterDropdown :options="filterOptions" />

    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('User') }}</th>
            <th>{{ trans('Plan') }}</th>
            <th>{{ trans('Expire At') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Date') }}</th>
            <th>
              <div class="text-end">
                {{ trans('Action') }}
              </div>
            </th>
          </tr>
        </thead>

        <tbody v-if="users.total">
          <tr v-for="user in users.data" :key="user.id">
            <td class="text-left">
              <div class="flex gap-2">
                <img
                  class="mr-1 inline w-12 rounded-full"
                  v-lazy="uiAvatar(user.name, user.avatar)"
                  alt="preview"
                />
                <a :href="route('admin.users.show', user.id)">
                  <p class="font-bold">
                    {{ user.name }}
                  </p>
                  <p>
                    {{ user.email }}
                  </p>
                </a>
              </div>
            </td>
            <td>
              <span v-if="user.plan" class="badge badge-primary"> {{ user.plan.title }}</span>
              <template v-else> N/A </template>
            </td>
            <td>
              {{ user.will_expire ? moment(user.will_expire).format('D MMM, YYYY') : trans('N/A') }}
            </td>

            <td class="text-left">
              <span class="badge" :class="user.status == 1 ? 'badge-success' : 'badge-danger'">
                {{ user.status == 1 ? trans('Active') : trans('Inactive') }}
              </span>
            </td>

            <td>
              {{ moment(user.created_at).format('D MMM, YYYY') }}
            </td>
            <td class="">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                </div>
                <div class="dropdown-content w-32">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link :href="route('admin.users.show', user)" class="dropdown-link">
                        <Icon icon="bx:show"></Icon>
                        <span>{{ trans('View') }}</span>
                      </Link>
                    </li>

                    <li class="dropdown-list-item">
                      <button @click="openEditUserModal(user)" class="dropdown-link">
                        <Icon icon="bx:user-pin"></Icon>
                        <span>{{ trans('Assign Plan') }}</span>
                      </button>
                    </li>

                    <li class="dropdown-list-item">
                      <Link :href="route('admin.users.edit', user)" class="dropdown-link">
                        <Icon icon="bx:edit"></Icon>
                        <span>{{ trans('Edit') }}</span>
                      </Link>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        as="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.users.destroy', user.id))"
                      >
                        <Icon class="h-6" icon="bx:trash" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else for-table="true" />
      </table>

      <Pagination :links="users.links" />
    </div>
  </div>

  <Modal
    state="editUserModal"
    :header-state="true"
    :header-title="trans('Assign Plan')"
    :action-btn-text="trans('Update')"
    :action-btn-state="true"
    :action-processing="editForm.processing"
    @action="updateUser"
  >
    <div class="mb-2">
      <label> {{ trans('Name') }}</label>
      <input type="text" class="input" disabled v-model="editForm.name" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Email') }}</label>
      <input type="email" class="input" disabled v-model="editForm.email" />
    </div>

    <div class="mb-2">
      <label class="label mb-2">{{ trans('Plan') }}</label>
      <select v-model="editForm.plan_id" class="select">
        <option value="">{{ trans('Select Plan') }}</option>
        <option v-for="plan in plans" :value="plan.id" :key="plan.id">
          {{ plan.title }} {{ formatCurrency(plan.price) }}
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label>{{ trans('Will Expire') }}</label>
      <input type="date" class="input" v-model="editForm.will_expire" />
    </div>
  </Modal>
</template>
