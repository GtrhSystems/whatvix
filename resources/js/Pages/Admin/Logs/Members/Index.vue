<script setup>
import { computed } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

const { deleteRow } = sharedComposable()

defineOptions({ layout: AdminLayout })

const props = defineProps(['members'])

const membersData = computed(() => props.members.data)
const hasMembers = computed(() => membersData.value.length > 0)
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th class="w-1/3">{{ trans('User') }}</th>
          <th class="w-1/3">{{ trans('Invited By') }}</th>
          <th>{{ trans('Workspaces') }}</th>
          <th>{{ trans('Created At') }}</th>
          <th class="!text-right">{{ trans('Action') }}</th>
        </tr>
      </thead>
      <tbody v-if="hasMembers" class="tbody">
        <tr v-for="user in membersData" :key="user.id">
          <td>
            <Link :href="route('admin.users.show', user)">
              <div class="flex items-center gap-2">
                <img :src="user.avatar" class="mr-3 h-8 w-8 rounded-full" alt="User Avatar" />
                <div>
                  <b>{{ user.name }}</b>
                  <p>{{ user.email }}</p>
                </div>
              </div>
            </Link>
          </td>
          <td>
            <Link v-if="user.invited_by" :href="route('admin.users.show', user.invited_by)">
              <div class="flex items-center gap-2">
                <img
                  :src="user.invited_by.avatar"
                  class="mr-3 h-8 w-8 rounded-full"
                  alt="Inviter Avatar"
                />
                <div>
                  <b>{{ user.invited_by.name }}</b>
                  <p>{{ user.invited_by.email }}</p>
                </div>
              </div>
            </Link>
            <span v-else>N/A</span>
          </td>
          <td>
            {{ user.assigned_workspaces?.map((workspace) => workspace.name).join(', ') || 'N/A' }}
          </td>
          <td>
            {{ new Date(user.created_at).toLocaleDateString() }}
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
                      <Link class="dropdown-link" :href="route('admin.logs.members.edit', user)">
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:edit" />
                        {{ trans('Edit') }}
                      </Link>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        type="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.logs.members.destroy', user))"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
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
      <Paginate :links="members.links" />
    </div>
  </div>
</template>
