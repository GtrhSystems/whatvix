<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

const props = defineProps(['segments', 'buttons', 'roles'])

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()
</script>
<template>
  <div class="space-y-6">
    <div class="table-responsive rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th width="10%">{{ trans('Name') }}</th>
            <th width="80%">{{ trans('Permissions') }}</th>
            <th width="10%" class="text-right">
              {{ trans('Action') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="roles.length">
          <tr v-for="role in roles" :key="role.id">
            <td>
              {{ role.name }}
            </td>
            <td>
              <span
                class="badge badge-primary mb-2 mr-1"
                v-for="perm in role.permissions"
                :key="perm.name"
              >
                {{ perm.name }}
              </span>
            </td>
            <td>
              <div class="flex justify-end">
                <Link :href="route('admin.role.edit', role.id)" class="btn btn-sm btn-primary mr-2">
                  <Icon icon="fe:edit" class="size-4" />
                </Link>
                <button
                  type="button"
                  @click="deleteRow(route('admin.role.destroy', role.id))"
                  class="btn btn-sm btn-danger"
                >
                  <Icon icon="bx:trash" class="size-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else :for-table="true" />
      </table>
    </div>
  </div>
</template>
