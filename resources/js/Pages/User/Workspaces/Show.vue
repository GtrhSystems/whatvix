<script setup>
import PageHeader from '@/Layouts/Admin/PageHeader.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'
import MultiSelect from '@vueform/multiselect'
import { useModalStore } from '@/Store/modalStore'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import sharedComposable from '@/Composables/sharedComposable'

const { deleteRow } = sharedComposable()

const modalStore = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['workspace', 'members', 'allMembers'])

const addTeamMemberForm = useForm({
  member_ids: []
})

const submit = () => {
  addTeamMemberForm.put(route('user.workspaces.update', props.workspace.id), {
    onSuccess: () => {
      addTeamMemberForm.reset()
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
          <th>{{ trans('Team Member') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="members.data.length" class="tbody">
        <tr v-for="(user, index) in members.data" :key="index">
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
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <a
                        class="dropdown-link"
                        @click="
                          deleteRow(
                            route('user.workspaces.members.remove', {
                              workspace: workspace.id,
                              member: user.id
                            })
                          )
                        "
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                        {{ trans('Remove') }}
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
      <Paginate :links="members.links" />
    </div>
  </div>

  <Modal
    :header-state="true"
    :header-title="trans('Add Member to this workspace')"
    state="assignToWorkspace"
  >
    <form @submit.prevent="submit" class="space-y-4">
      <MultiSelect
        v-model="addTeamMemberForm.member_ids"
        :options="allMembers"
        :searchable="true"
        mode="tags"
        valueProp="id"
        trackBy="name"
        label="name"
        class="multiselect-dark"
      />

      <div class="text-end">
        <SpinnerBtn :processing="addTeamMemberForm.processing" :btn-text="trans('Add Members')" />
      </div>
    </form>
  </Modal>
</template>
