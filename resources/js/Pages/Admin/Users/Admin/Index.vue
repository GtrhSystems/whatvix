<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import { Head, Link } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
const props = defineProps(['buttons', 'users'])
defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()
</script>

<template>
  <div class="space-y-6">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Name') }}</th>
            <th>{{ trans('Email') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Role') }}</th>
            <th class="!text-right">{{ trans('Action') }}</th>
          </tr>
        </thead>
        <tbody v-if="users.length">
          <tr v-for="row in users" :key="row.id">
            <td>
              {{ row.name }}
            </td>
            <td>
              {{ row.email }}
            </td>
            <td>
              <span :class="row.status == 1 ? 'badge badge-success' : 'badge badge-danger'">
                {{ row.status == 1 ? trans('Active') : trans('Inactive') }}
              </span>
            </td>
            <td>
              <span class="badge badge-primary" v-for="r in row.roles" :key="r.id">{{
                r.name
              }}</span>
            </td>
            <td>
              <div class="flex justify-end gap-2">
                <Link :href="route('admin.admin.edit', row.id)" class="btn btn-sm btn-primary">
                  <Icon icon="bx:edit" class="size-4" />
                </Link>
                <button
                  type="button"
                  @click="deleteRow(route('admin.admin.destroy', row.id))"
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
