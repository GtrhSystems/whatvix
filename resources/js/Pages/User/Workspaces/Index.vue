<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const { deleteRow } = sharedComposable()

const props = defineProps(['workspaces'])
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
          <th>{{ trans('Name') }}</th>
          <th>{{ trans('Authority') }}</th>
          <th>{{ trans('Description') }}</th>
          <th>{{ trans('Modules') }}</th>
          <th>
            {{ trans('Total Members') }}
          </th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="workspaces.data.length" class="tbody">
        <tr v-for="(workspace, index) in workspaces.data" :key="index">
          <td>
            {{ workspace.name }}
          </td>
          <td>
            <div class="badge" :class="workspace.is_owner ? 'badge-primary' : 'badge-secondary'">
              {{ workspace.is_owner ? 'Owner' : 'Member' }}
            </div>
          </td>
          <td>
            {{ workspace.description || 'N/A' }}
          </td>
          <td>
            {{ workspace.modules?.join(', ') }}
          </td>
          <td>
            <Link v-if="workspace.is_owner" :href="route('user.workspaces.show', workspace)">
              {{ workspace.members_count ?? 0 }} {{ trans('Members') }}
            </Link>
            <span v-else>{{ workspace.members_count ?? 0 }} {{ trans('Members') }}</span>
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <template v-if="workspace.is_owner">
                      <li class="dropdown-list-item">
                        <Link
                          class="dropdown-link"
                          :href="route('user.workspaces.show', workspace.id)"
                        >
                          <Icon class="h-5 text-3xl text-slate-400" icon="bxs:user-detail" />
                          {{ trans('Manage Team') }}
                        </Link>
                      </li>
                      <li class="dropdown-list-item">
                        <Link
                          class="dropdown-link"
                          :href="route('user.workspaces.edit', workspace.id)"
                        >
                          <Icon class="h-5 text-3xl text-slate-400" icon="bx:edit" />
                          {{ trans('Edit') }}
                        </Link>
                      </li>
                      <li class="dropdown-list-item">
                        <a
                          class="dropdown-link"
                          @click="deleteRow(route('user.workspaces.destroy', workspace.id))"
                        >
                          <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                          {{ trans('Delete') }}
                        </a>
                      </li>
                    </template>
                    <template v-else>
                      <li class="dropdown-list-item">
                        <a
                          class="dropdown-link"
                          @click="deleteRow(route('user.workspaces.leave', workspace.id))"
                        >
                          <Icon class="h-5 text-3xl text-slate-400" icon="bx:log-out" />
                          {{ trans('Leave') }}
                        </a>
                      </li>
                    </template>
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
      <Paginate :links="workspaces.links" />
    </div>
  </div>
</template>
