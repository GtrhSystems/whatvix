<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import trans from '@/Composables/transComposable'
import Pagination from '@/Components/Dashboard/Paginate.vue'

import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import sharedComposable from '@/Composables/sharedComposable'
import moment from 'moment'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const { deleteRow } = sharedComposable()

const props = defineProps(['states', 'counter', 'countries'])

const filterOptions = [
  {
    value: 'name',
    label: 'State Name'
  },
  {
    value: 'country_name',
    label: 'Country Name'
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Active',
        value: 'active'
      },
      {
        label: 'Inactive',
        value: 'inactive'
      }
    ]
  }
]

const stats = [
  {
    value: props.counter.total,
    title: trans('Total'),
    iconClass: 'bx bx-list-ol'
  },
  {
    value: props.counter.active,
    title: trans('Active'),
    iconClass: 'bx bx-badge-check'
  },
  {
    value: props.counter.inactive,
    title: trans('Inactive'),
    iconClass: 'bx bx-x'
  }
]

const storeForm = useForm({
  country_id: '',
  name: '',
  status: 'active'
})

const storeItem = () => {
  storeForm.post(route('admin.states.store'), {
    onSuccess: () => drawer.of('#addNewItemDrawer').hide(),
    onFinish: () => form.reset()
  })
}

const updateForm = useForm({
  id: null,
  country_id: '',
  name: '',
  status: 'active'
})

const setEditForm = (item) => {
  updateForm.id = item.id
  updateForm.country_id = item.country_id
  updateForm.name = item.name
  updateForm.status = item.status
  drawer.of('#updateItemDrawer').show()
}

const updateItem = () => {
  updateForm.put(route('admin.states.update', updateForm.id), {
    onSuccess: () => updateForm.reset(),
    onFinish: () => drawer.of('#updateItemDrawer').hide()
  })
}
</script>

<template>
  <Drawer
    id="addNewItemDrawer"
    :title="trans('Add New State')"
    :handleSubmit="storeItem"
    :processing="storeForm.processing"
  >
    <div class="mb-2">
      <label>{{ trans('Country') }}</label>
      <select v-model="storeForm.country_id" class="select">
        <option value="">{{ trans('SELECT COUNTRY') }}</option>
        <option v-for="country in countries" :key="country.id" :value="country.id">
          {{ country.name }} ({{ country.code }})
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label>{{ trans('State Name') }}</label>
      <input v-model="storeForm.name" class="input" placeholder="California" />
    </div>

    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="storeForm.status" class="select">
        <option value="active">{{ trans('Active') }}</option>
        <option value="inactive">{{ trans('Inactive') }}</option>
      </select>
    </div>
  </Drawer>

  <Drawer
    id="updateItemDrawer"
    :title="trans('Edit State')"
    :handleSubmit="updateItem"
    :processing="updateForm.processing"
  >
    <div class="mb-2">
      <label>{{ trans('Country') }}</label>
      <select v-model="updateForm.country_id" class="select">
        <option value="">{{ trans('SELECT COUNTRY') }}</option>
        <option v-for="country in countries" :key="country.id" :value="country.id">
          {{ country.name }} ({{ country.code }})
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label>{{ trans('State Name') }}</label>
      <input v-model="updateForm.name" class="input" placeholder="California" />
    </div>

    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="updateForm.status" class="select">
        <option value="active">{{ trans('Active') }}</option>
        <option value="inactive">{{ trans('Inactive') }}</option>
      </select>
    </div>
  </Drawer>

  <FilterDropdown :action="route('admin.states.index')" :options="filterOptions" />

  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('#') }}</th>
          <th>{{ trans('State Name') }}</th>
          <th>{{ trans('Country Name') }}</th>
          <th>{{ trans('Status') }}</th>
          <th>{{ trans('Created At') }}</th>
          <th class="!text-end">
            {{ trans('Actions') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="states.total">
        <tr v-for="state in states.data" :key="state.id">
          <td>{{ state.id }}</td>
          <td>{{ state.name }}</td>
          <td>{{ state.country?.name ?? 'na' }}</td>
          <td>
            <div
              :class="[state.status == 'active' ? 'badge-primary' : 'badge-secondary']"
              class="badge capitalize"
            >
              {{ state.status }}
            </div>
          </td>
          <td class="text-center">
            {{ moment(state.created_at).format('DD MMM, YYYY') }}
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
                      <button @click="setEditForm(state)" class="dropdown-link">
                        <Icon class="h-6" icon="bx:pencil" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        type="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.states.destroy', state))"
                      >
                        <Icon class="h-6" icon="material-symbols:delete-outline" />
                        <span>{{ trans('Delete') }}</span>
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
  <Pagination :links="states.links" />
</template>
