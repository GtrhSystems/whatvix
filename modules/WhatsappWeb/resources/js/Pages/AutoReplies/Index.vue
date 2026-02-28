<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import NotificationRing from '@/Components/Chats/NotificationRing.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['autoReplies'])
const { deleteRow, textExcerpt } = sharedComposable()
</script>

<template>
  <PageHeader />
  <NotificationRing module="whatsapp-web" />
  <div class="table-responsive w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Device Name') }}
          </th>
          <th>{{ trans('Keywords') }}</th>
          <th>{{ trans('Message Type') }}</th>
          <th>{{ trans('Message Template') }}</th>
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
            {{ autoReply.keywords.join(', ') }}
          </td>
          <td>
            <span v-if="autoReply.message_type == 'text'" class="badge badge-success capitalize">{{
              autoReply.message_type
            }}</span>

            <span
              v-else-if="autoReply.message_type == 'template'"
              class="badge badge-info capitalize"
            >
              {{ autoReply.message_type }}</span
            >

            <span v-else class="badge badge-primary">{{ autoReply.message_type }}</span>
          </td>
          <td>
            <a
              class="flex items-center gap-1 font-bold text-blue-600 hover:underline"
              v-if="autoReply.message_type == 'template'"
              target="_blank"
              :href="`/user/whatsapp-web/templates/${autoReply?.template?.id}`"
            >
              {{ autoReply?.template?.name }}
              <Icon icon="bx:link-external" />
            </a>

            <span v-else> {{ textExcerpt(autoReply.message_template, 50) }}</span>
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
                      <Link
                        class="dropdown-link"
                        :href="route('user.whatsapp-web.auto-replies.edit', autoReply.id)"
                      >
                        <Icon icon="bx:bxs-edit-alt" />
                        <span>{{ trans('Edit') }}</span>
                      </Link>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        @click="
                          deleteRow(route('user.whatsapp-web.auto-replies.destroy', autoReply.id))
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
