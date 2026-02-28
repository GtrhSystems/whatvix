<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import trans from '@/Composables/transComposable'
import Pagination from '@/Components/Dashboard/Paginate.vue'

import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import sharedComposable from '@/Composables/sharedComposable'
import moment from 'moment'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AjaxLoader from '@/Components/AjaxLoader.vue'

defineOptions({ layout: AdminLayout })

const { deleteRow } = sharedComposable()

const props = defineProps(['cities', 'counter', 'countries'])

const states = ref([])

const filterOptions = [
  {
    value: 'name',
    label: 'City Name'
  },
  {
    value: 'postal_code',
    label: 'Postal Code'
  },
  {
    value: 'state_name',
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

const storeForm = useForm({
  country_id: '',
  state_id: '',
  name: '',
  postal_code: '',
  status: 'active'
})

const storeItem = () => {
  storeForm.post(route('admin.cities.store'), {
    onSuccess: () => drawer.of('#addNewItemDrawer').hide(),
    onFinish: () => form.reset()
  })
}

const updateForm = useForm({
  id: null,
  country_id: '',
  state_id: '',
  name: '',
  postal_code: '',
  status: 'active'
})

const setEditForm = (item) => {
  updateForm.id = item.id
  updateForm.country_id = item.country_id
  updateForm.state_id = item.state_id
  updateForm.name = item.name
  updateForm.postal_code = item.postal_code
  updateForm.status = item.status
  getStates(updateForm.country_id)
  drawer.of('#updateItemDrawer').show()
}

const updateItem = () => {
  updateForm.put(route('admin.cities.update', updateForm.id), {
    onSuccess: () => updateForm.reset(),
    onFinish: () => drawer.of('#updateItemDrawer').hide()
  })
}

const getting = {
  states: false
}

const getStates = (country_id) => {
  if (country_id) {
    getting.states = true
    storeForm.reset('country_id')
    axios.get(`/api/get-states-by-country/${country_id}`).then((res) => {
      states.value = res.data
      getting.states = false
    })
  }
}
</script>

<template>
  <Drawer
    id="addNewItemDrawer"
    :title="trans('Add New City')"
    :handleSubmit="storeItem"
    :processing="storeForm.processing"
  >
    <div class="mb-2">
      <label>{{ trans('Country') }}</label>
      <select v-model="storeForm.country_id" @input="getStates($event.target.value)" class="select">
        <option value="">{{ trans('SELECT COUNTRY') }}</option>
        <option v-for="country in countries" :key="country.id" :value="country.id">
          {{ country.name }} ({{ country.code }})
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label class="flex">
        <span> {{ trans('State') }}</span>
        <AjaxLoader v-if="getting.states" />
      </label>
      <select v-model="storeForm.state_id" class="select" :disabled="getting.states">
        <option value="">{{ trans('SELECT STATE') }}</option>
        <option v-for="state in states" :key="state.id" :value="state.id">
          {{ state.name }}
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label>{{ trans('City Name') }}</label>
      <input v-model="storeForm.name" class="input" placeholder="New York" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Postal Code') }}</label>
      <input v-model="storeForm.postal_code" class="input" placeholder="10001" />
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
    :title="trans('Edit City')"
    :handleSubmit="updateItem"
    :processing="updateForm.processing"
  >
    <div class="mb-2">
      <label>{{ trans('Country') }}</label>
      <select
        v-model="updateForm.country_id"
        @input="getStates($event.target.value)"
        class="select"
      >
        <option value="">{{ trans('SELECT COUNTRY') }}</option>
        <option v-for="country in countries" :key="country.id" :value="country.id">
          {{ country.name }} ({{ country.code }})
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label class="flex">
        <span> {{ trans('State') }}</span>

        <span v-if="getting.states">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            width="24px"
            height="24px"
            viewBox="0 0 100 100"
            preserveAspectRatio="xMidYMid"
          >
            <g transform="rotate(0 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.9285714285714286s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(25.714285714285715 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.8571428571428571s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(51.42857142857143 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.7857142857142857s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(77.14285714285714 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.7142857142857143s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(102.85714285714286 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.6428571428571429s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(128.57142857142858 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.5714285714285714s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(154.28571428571428 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.5s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(180 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.42857142857142855s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(205.71428571428572 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.35714285714285715s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(231.42857142857142 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.2857142857142857s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(257.14285714285717 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.21428571428571427s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(282.85714285714283 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.14285714285714285s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(308.57142857142856 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="-0.07142857142857142s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
            <g transform="rotate(334.2857142857143 50 50)">
              <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#000">
                <animate
                  attributeName="opacity"
                  values="1;0"
                  keyTimes="0;1"
                  dur="1s"
                  begin="0s"
                  repeatCount="indefinite"
                ></animate>
              </rect>
            </g>
          </svg>
        </span>
      </label>
      <select v-model="updateForm.state_id" class="select" :disabled="getting.states">
        <option value="">{{ trans('SELECT STATE') }}</option>
        <option v-for="state in states" :key="state.id" :value="state.id">
          {{ state.name }}
        </option>
      </select>
    </div>

    <div class="mb-2">
      <label>{{ trans('City Name') }}</label>
      <input v-model="updateForm.name" class="input" placeholder="California" />
    </div>

    <div class="mb-2">
      <label>{{ trans('Postal Code') }}</label>
      <input v-model="updateForm.postal_code" class="input" placeholder="10001" />
    </div>

    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="updateForm.status" class="select">
        <option value="active">{{ trans('Active') }}</option>
        <option value="inactive">{{ trans('Inactive') }}</option>
      </select>
    </div>
  </Drawer>

  <FilterDropdown :action="route('admin.cities.index')" :options="filterOptions" />

  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('#') }}</th>
          <th>{{ trans('City - Postal Code') }}</th>
          <th>{{ trans('State') }}</th>
          <th>{{ trans('Country') }}</th>
          <th>{{ trans('Status') }}</th>
          <th>{{ trans('Created At') }}</th>
          <th class="!text-end">
            {{ trans('Actions') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="cities.total">
        <tr v-for="city in cities.data" :key="city.id">
          <td>{{ city.id }}</td>
          <td>{{ city.name + ' - ' + city.postal_code }}</td>
          <td>{{ city.state?.name ?? 'na' }}</td>
          <td>{{ city.country?.name ?? 'na' }}</td>
          <td>
            <div
              :class="[city.status == 'active' ? 'badge-primary' : 'badge-secondary']"
              class="badge capitalize"
            >
              {{ city.status }}
            </div>
          </td>
          <td class="text-center">
            {{ moment(city.created_at).format('DD MMM, YYYY') }}
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
                      <button @click="setEditForm(city)" class="dropdown-link">
                        <Icon class="h-6" icon="bx:pencil" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        type="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.cities.destroy', city))"
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
  <Pagination :links="cities.links" />
</template>
