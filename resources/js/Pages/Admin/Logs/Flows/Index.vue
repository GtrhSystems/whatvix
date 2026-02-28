<script setup>
import moment from 'moment'

import { useForm } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

defineOptions({ layout: AdminLayout })
const props = defineProps(['flows'])
const { deleteRow, textExcerpt, badgeClass } = sharedComposable()
</script>

<template>
  <div class="table-responsive w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('ID') }}
          </th>
          <th>
            {{ trans('Title') }}
          </th>
          <th>
            {{ trans('Created At') }}
          </th>
          <th class="!text-right">
            {{ trans('Status') }}
          </th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="flows?.data.length" class="tbody">
        <tr v-for="(flow, index) in flows.data" :key="index">
          <td>{{ flow?.id }}</td>
          <td>
            <a target="_blank" :href="`/user/whatsapp/chatbot-flow/${flow?.id}`">
              {{ flow?.title }}
            </a>
          </td>
          <td>{{ moment(flow?.created_at).fromNow() }}</td>
          <td class="!text-right">
            <span class="capitalize" :class="badgeClass(flow.status)">
              {{ flow.status }}
            </span>
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
                        @click="deleteRow(route('admin.logs.flows.destroy', flow.id))"
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

    <Paginate :links="flows.links" />
  </div>
</template>
