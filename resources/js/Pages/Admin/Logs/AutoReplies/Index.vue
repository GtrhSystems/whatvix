<script setup>
import { Icon } from '@iconify/vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

defineOptions({ layout: AdminLayout })
const props = defineProps(['autoReplies'])
const { deleteRow, textExcerpt } = sharedComposable()
</script>

<template>
  <div class="table-responsive w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Device Name') }}
          </th>
          <th>{{ trans('Message Type') }}</th>
          <th>{{ trans('Keywords') }}</th>
          <th>{{ trans('Message') }}</th>
          <th>{{ trans('Owner') }}</th>
          <th class="!text-right">
            {{ trans('Status') }}
          </th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="autoReplies.data.length" class="tbody">
        <tr v-for="(autoReply, index) in autoReplies.data" :key="index">
          <td>
            {{ autoReply.platform?.name }}
          </td>
          <td>
            <span class="badge badge-primary">{{ autoReply.message_type }}</span>
          </td>
          <td>
            {{ autoReply.keywords.join(', ') }}
          </td>
          <td>
            {{
              autoReply.message_type == 'template'
                ? textExcerpt(autoReply.message_template ?? autoReply.message_type, 50)
                : autoReply.message_type
            }}
          </td>

          <td>
            <Link :href="route('admin.users.show', autoReply.owner)">
              {{ autoReply.owner?.name }}
            </Link>
          </td>

          <td class="!text-right">
            <span v-if="autoReply.status == 'active'" class="badge badge-success">
              {{ autoReply.status }}</span
            >
            <span v-else class="badge badge-warning">{{ autoReply.status }}</span>
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
                        @click="
                          deleteRow(route('user.whatsapp.auto-replies.destroy', autoReply.id))
                        "
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

    <Paginate :links="autoReplies.links" />
  </div>
</template>
