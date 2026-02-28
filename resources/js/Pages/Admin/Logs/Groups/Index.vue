<script setup>
import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable.js'

import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useModalStore } from '@/Store/modalStore'

const modal = useModalStore()

defineOptions({ layout: AdminLayout })
const props = defineProps(['groups'])
const { deleteRow } = sharedComposable()
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Group') }}
          </th>
          <th>
            {{ trans('Members') }}
          </th>
          <th>{{ trans('Module') }}</th>
          <th class="!text-right">{{ trans('Owner') }}</th>

          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="groups.data.length" class="tbody">
        <tr v-for="(group, index) in groups.data" :key="index">
          <td>
            {{ group.name }}
          </td>
          <td>
            {{ group.customers_count }}
          </td>
          <td>{{ group.module }}</td>
          <td class="!text-right">
            <Link :href="route('admin.users.show', group.owner)">
              {{ group.owner?.name }}
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
                        href="#"
                        @click="deleteRow(route('admin.logs.groups.destroy', group))"
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
</template>
