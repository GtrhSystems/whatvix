<script setup>
import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable.js'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'

const modal = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['groups', 'dialCodes', 'customers', 'platforms'])
const { deleteRow } = sharedComposable()

const importContactForm = useForm({
  csv_file: null,
  group_id: '',
  platform_id: ''
})

const importContacts = () => {
  importContactForm.post(route('user.whatsapp.customers.bulk-import'), {
    onSuccess: () => {
      modal.close('importModal')
      importContactForm.reset()
    }
  })
}
const filterOptions = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Phone',
    value: 'uuid'
  }
]
</script>

<template>
  <FilterDropdown :options="filterOptions" />
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Name') }}</th>
          <th>
            {{ trans('Phone') }}
          </th>
          <th>{{ trans('Groups') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="customers.data.length" class="tbody">
        <tr v-for="(customer, index) in customers.data" :key="index">
          <td>
            <div class="flex items-center gap-2">
              <img
                :src="customer.picture ?? 'https://ui-avatars.com/api/?name=' + customer.name"
                class="h-8 w-8 rounded-full"
              />
              <span>{{ customer.name }}</span>
            </div>
          </td>
          <td>
            {{ customer.uuid }}
          </td>
          <td>
            {{ customer.groups.map((g) => g.name).join(', ') || 'N/A' }}
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link
                        :href="route('user.whatsapp.customers.edit', customer)"
                        class="dropdown-link"
                      >
                        <Icon icon="bx:edit" />
                        {{ trans('Edit') }}
                      </Link>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        @click="deleteRow(route('user.whatsapp.customers.destroy', customer.id))"
                      >
                        <Icon icon="bx:trash" />
                        {{ trans('Delete') }}
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
    <div class="w-full">
      <Paginate v-if="customers.data.length" :links="customers.links" />
    </div>
  </div>

  <Modal state="importModal" :header-state="true" header-title="Import customers">
    <form @submit.prevent="importContacts">
      <div class="w-full">
        <label class="label mb-1"
          >{{ trans('Select CSV') }}
          <a href="/assets/whatsapp-customers-sample.csv" download="">{{
            trans('(Download Sample)')
          }}</a></label
        >
        <input
          type="file"
          accept=".csv"
          @change="($event) => (importContactForm.csv_file = $event.target.files[0])"
          class="input"
        />

        <small class="text-red-600" v-if="importContactForm.errors.csv_file">{{
          importContactForm.errors.csv_file
        }}</small>
      </div>

      <div class="w-full">
        <label class="label mb-1">{{ trans('Platforms') }}</label>
        <select v-model="importContactForm.platform_id" class="select">
          <option value="">{{ trans('Select Platform') }}</option>
          <option v-for="(platform, index) in platforms" :key="index" :value="platform.id">
            {{ platform.name }}
          </option>
        </select>

        <small class="text-red-600" v-if="importContactForm.errors.group_id">{{
          importContactForm.errors.group_id
        }}</small>
      </div>
      <div class="w-full">
        <label class="label mb-1">{{ trans('Group') }}</label>
        <select v-model="importContactForm.group_id" class="select">
          <option value="">{{ trans('Select Group') }}</option>
          <option v-for="(group, index) in groups" :key="index" :value="group.id">
            {{ group.name }}
          </option>
        </select>

        <small class="text-red-600" v-if="importContactForm.errors.group_id">{{
          importContactForm.errors.group_id
        }}</small>
      </div>
      <div class="mt-2">
        <SpinnerBtn classes="btn btn-primary w-full" :processing="importContactForm.processing" />
      </div>
    </form>
  </Modal>
</template>
