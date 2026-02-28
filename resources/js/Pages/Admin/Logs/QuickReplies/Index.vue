<script setup>
import List from '@/Components/User/QuickReplies/List.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

const { textExcerpt, badgeClass } = sharedComposable()

defineOptions({ layout: AdminLayout })
const props = defineProps(['quickReplies'])
</script>
<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('#') }}</th>
          <th>{{ trans('Message Template') }}</th>
          <th>{{ trans('Owner') }}</th>
          <th>{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="quickReplies.data.length" class="tbody">
        <tr v-for="(template, index) in quickReplies.data" :key="index">
          <td>
            {{ index + 1 }}
          </td>
          <td>
            {{ textExcerpt(template.message_template, 120) }}
          </td>
          <td>
            <Link :href="route('admin.users.show', template.owner)">{{
              template.owner?.name
            }}</Link>
          </td>
          <td>
            <span :class="badgeClass(template.status)">{{
              template.status === 'active' ? 'active' : 'inactive'
            }}</span>
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
                          deleteRow(route(`user.${module}.quick-replies.destroy`, template.id))
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
    <div class="w-full">
      <Paginate v-if="quickReplies.data.length" :links="quickReplies.links" />
    </div>
  </div>
</template>
