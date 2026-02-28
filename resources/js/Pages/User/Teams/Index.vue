<script setup>
import { ref } from 'vue'

import { useModalStore } from '@/Store/modalStore'

import Modal from '@/Components/Dashboard/Modal.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import { useForm } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import MultiSelect from '@vueform/multiselect'

const { deleteRow } = sharedComposable()

defineOptions({ layout: UserLayout })

const modalStore = useModalStore()
const props = defineProps(['myWorkspaces', 'myTeamMembers'])

const selectedUser = ref(null)

const openModalFor = (user) => {
  selectedUser.value = user
  form.workspace_ids = user.assigned_workspaces?.map((workspace) => workspace.id)
  modalStore.open('assignToWorkspace')
}

const form = useForm({
  workspace_ids: []
})

const submit = () => {
  form.put(route('user.teams.update', selectedUser.value.id), {
    onSuccess: () => {
      form.reset()
      modalStore.close('assignToWorkspace')
    }
  })
}
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th class="w-[5%]">{{ trans('ID') }}</th>
          <th>{{ trans('User') }}</th>
          <th>{{ trans('Workspaces') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="myTeamMembers.data.length" class="tbody">
        <tr v-for="(user, index) in myTeamMembers.data" :key="index">
          <td>{{ user.id }}</td>
          <td>
            <div class="flex items-center gap-2">
              <img :src="user.avatar" class="mr-3 h-8 w-8 rounded-full" />
              <div>
                <b> {{ user.name }}</b>
                <p>{{ user.email }}</p>
              </div>
            </div>
          </td>
          <td>
            {{ user.assigned_workspaces?.map((workspace) => workspace.name).join(', ') }}
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
                      <a class="dropdown-link" @click="openModalFor(user)">
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:user-plus" />
                        {{ trans('Manage Workspaces') }}
                      </a>
                    </li>
                    <li class="dropdown-list-item">
                      <a
                        class="dropdown-link"
                        @click="deleteRow(route('user.teams.destroy', user.id))"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                        {{ trans('Delete') }}
                      </a>
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
      <Paginate :links="myTeamMembers.links" />
    </div>
  </div>

  <Modal
    modal-type="sidebar"
    :header-state="true"
    :header-title="trans('Assign Workspaces to User')"
    state="assignToWorkspace"
    modal-size="w-1/3 2xl:w-1/4"
  >
    <form @submit.prevent="submit" class="space-y-4">
      <MultiSelect
        v-model="form.workspace_ids"
        :options="myWorkspaces"
        :searchable="true"
        mode="tags"
        valueProp="id"
        trackBy="name"
        label="name"
        class="multiselect-dark"
      />

      <SpinnerBtn
        class="w-full"
        icon="bx:send"
        :processing="form.processing"
        :btn-text="trans('Update Members')"
      />
    </form>
  </Modal>
</template>
