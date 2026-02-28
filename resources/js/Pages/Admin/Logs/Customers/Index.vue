<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable.js'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps(['customers'])
const { deleteRow } = sharedComposable()
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Name') }}</th>
          <th>
            {{ trans('Phone') }}
          </th>
          <th>{{ trans('Groups') }}</th>
          <th>{{ trans('Module') }}</th>
          <th>{{ trans('Owner') }}</th>
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
            {{ customer.module }}
          </td>
          <td>
            <Link :href="route('admin.users.show', customer.owner)">
              {{ customer.owner?.name }}
            </Link>
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
                      <button
                        class="dropdown-link delete-confirm"
                        @click="deleteRow(route('admin.logs.customers.destroy', customer.id))"
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
</template>
