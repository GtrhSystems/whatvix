<script setup>
import MultiSelect from '@/Components/Forms/MultiSelect.vue'
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
const props = defineProps(['groups', 'platforms', 'customers', 'scraped_record'])
const { deleteRow } = sharedComposable()

const importFromDeviceForm = useForm({
  platform_ids: [],
  group_ids: []
})
const importFromScrapeDataForm = useForm({
  scraped_record_ids: [],
  group_ids: []
})

const importFromDeviceFromSubmit = () => {
  importFromDeviceForm.post(route('user.whatsapp-web.customers.import-from-device'), {
    onSuccess: () => {
      modal.close('importFromDeviceModal')
      importFromDeviceForm.reset()
    }
  })
}
const importFromScrapeDataSubmit = () => {
  importFromScrapeDataForm.post(route('user.whatsapp-web.customers.import-from-scraping'), {
    onSuccess: () => {
      modal.close('importFromScrapeDataModal')
      importFromScrapeDataForm.reset()
    }
  })
}

const importFromCsvFrom = useForm({
  csv_file: null,
  group_ids: []
})

const importFromCsvFromSubmit = () => {
  importFromCsvFrom.post(route('user.whatsapp-web.customers.bulk-import'), {
    onSuccess: () => {
      modal.close('importModal')
      importFromCsvFrom.reset()
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
          <th class="w-[25%]">{{ trans('Name') }}</th>
          <th>
            {{ trans('Phone') }}
          </th>
          <th class="!text-right">{{ trans('Groups') }}</th>
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
          <td class="!text-right">
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
                      <a
                        :href="route('user.whatsapp-web.customers.edit', customer)"
                        class="dropdown-link"
                      >
                        <Icon icon="bx:edit" />
                        {{ trans('Edit') }}
                      </a>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        href="#"
                        @click="
                          deleteRow(route('user.whatsapp-web.customers.destroy', customer.id))
                        "
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

  <Modal state="importFromDeviceModal" :header-state="true" header-title="Import from device">
    <form @submit.prevent="importFromDeviceFromSubmit">
      <div class="mb-2">
        <MultiSelect
          label="Import from"
          placeholder="Select Devices"
          v-model="importFromDeviceForm.platform_ids"
          :options="platforms"
        />

        <small class="text-red-600" v-if="importFromDeviceForm.errors.platform_ids">
          {{ importFromDeviceForm.errors.platform_ids }}</small
        >
      </div>

      <div class="mb-2">
        <MultiSelect
          label="Import to"
          placeholder="Select Groups"
          v-model="importFromDeviceForm.group_ids"
          :options="groups"
        />
        <small class="text-red-600" v-if="importFromDeviceForm.errors.group_ids">
          {{ importFromDeviceForm.errors.group_ids }}</small
        >
      </div>
      <div class="mt-2">
        <SpinnerBtn
          classes="btn btn-primary w-full"
          btn-text="Import Contacts"
          :processing="importFromDeviceForm.processing"
        />
      </div>
    </form>
  </Modal>

  <Modal state="importModal" :header-state="true" header-title="Import customers">
    <form @submit.prevent="importFromCsvFromSubmit">
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
          @change="($event) => (importFromCsvFrom.csv_file = $event.target.files[0])"
          class="input"
        />

        <small class="text-red-600" v-if="importFromCsvFrom.errors.csv_file">{{
          importFromCsvFrom.errors.csv_file
        }}</small>
      </div>

      <div class="mb-2">
        <MultiSelect
          label="Groups"
          placeholder="Select Groups"
          v-model="importFromCsvFrom.group_ids"
          :options="groups"
          input-label="label"
          valueProp="value"
        />
        <small class="text-red-600" v-if="importFromCsvFrom.errors.group_ids">
          {{ importFromCsvFrom.errors.group_ids }}</small
        >
      </div>
      <div class="mt-2">
        <SpinnerBtn classes="btn btn-primary w-full" :processing="importFromCsvFrom.processing" />
      </div>
    </form>
  </Modal>
  <Modal state="importFromScrapeDataModal" :header-state="true" header-title="Import contacts">
    <form @submit.prevent="importFromScrapeDataSubmit">
      <div class="mb-2">
        <MultiSelect
          label="Import from"
          placeholder="Select Scraped Records"
          v-model="importFromScrapeDataForm.scraped_record_ids"
          :options="scraped_record"
          input-label="label"
          valueProp="value"
        />

        <small class="text-red-600" v-if="importFromScrapeDataForm.errors.scraped_record_ids">
          {{ importFromDeviceForm.errors.scraped_record_ids }}</small
        >
      </div>

      <div class="mb-2">
        <MultiSelect
          label="Import to"
          placeholder="Select Groups"
          v-model="importFromScrapeDataForm.group_ids"
          :options="groups"
          input-label="label"
          valueProp="value"
        />
        <small class="text-red-600" v-if="importFromScrapeDataForm.errors.group_ids">
          {{ importFromScrapeDataForm.errors.group_ids }}</small
        >
      </div>
      <div class="mt-2">
        <SpinnerBtn
          classes="btn btn-primary w-full"
          btn-text="Import Contacts"
          :disabled="
            !importFromScrapeDataForm.group_ids.length ||
            !importFromScrapeDataForm.scraped_record_ids.length
          "
          :processing="importFromScrapeDataForm.processing"
        />
      </div>
    </form>
  </Modal>
</template>
