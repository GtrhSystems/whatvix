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
const props = defineProps(['device', 'groups'])
const { deleteRow } = sharedComposable()

const createFrom = useForm({
  name: ''
})

const createGroup = () => {
  createFrom.post(route('user.whatsapp.groups.store'), {
    onSuccess: () => {
      modal.close('groupCreate')
      createFrom.reset()
    }
  })
}

const editFrom = useForm({
  id: null,
  name: ''
})

const groupEdit = () => {
  editFrom.put(route('user.whatsapp.groups.update', { group: editFrom.id }), {
    onSuccess: () => modal.close('groupEdit')
  })
}

const setEditFormValue = (group) => {
  editFrom.id = group.id
  editFrom.name = group.name
  modal.open('groupEdit')
}
const filterOptions = [
  {
    label: 'Name',
    value: 'name'
  }
]
</script>

<template>
  <FilterDropdown :options="filterOptions" />
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th class="w-[40%]">
            {{ trans('Group Name') }}
          </th>
          <th class="w-[50%] !text-right">
            {{ trans('Total Customers') }}
          </th>

          <th class="w-[10%] !text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="groups.data.length" class="tbody">
        <tr v-for="(group, index) in groups.data" :key="index">
          <td>
            <div class="flex items-center gap-2">
              <img :src="'https://ui-avatars.com/api/?name=' + group.name" class="h-8 w-8" />
              <span>{{ group.name }}</span>
            </div>
          </td>
          <td class="!text-right">
            {{ group.customers_count }}
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
                      <a class="dropdown-link" href="#" @click="setEditFormValue(group)">
                        <Icon icon="bx:bxs-edit-alt" />
                        <span>{{ trans('Edit') }}</span>
                      </a>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        href="#"
                        @click="deleteRow(route('user.whatsapp.groups.destroy', group.id))"
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
      <Paginate v-if="groups.data.length" :links="groups.links" />
    </div>
  </div>

  <Modal state="groupCreate">
    <form @submit.prevent="createGroup">
      <div class="mb-2">
        <label>{{ trans('Name') }}</label>
        <input
          type="text"
          v-model="createFrom.name"
          class="input"
          placeholder="Group Name"
          required
        />
      </div>
      <div class="mt-2 text-end">
        <SpinnerBtn
          classes="btn btn-primary"
          :processing="createFrom.processing"
          :btn-text="trans('Create')"
        />
      </div>
    </form>
  </Modal>

  <Modal state="groupEdit">
    <form @submit.prevent="groupEdit">
      <div class="mb-2">
        <label>{{ trans('Name') }}</label>
        <input type="text" v-model="editFrom.name" class="input" />
      </div>
      <div class="mt-2 text-end">
        <SpinnerBtn
          classes="btn btn-primary"
          :processing="editFrom.processing"
          :btn-text="trans('Save Changes')"
        />
      </div>
    </form>
  </Modal>
</template>
