<script setup>
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import sharedComposable from '@/Composables/sharedComposable'

const { deleteRow } = sharedComposable()

defineOptions({ layout: UserLayout })

const props = defineProps(['qaReplies'])
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th>{{ trans('#') }}</th>
        <th>{{ trans('Question') }}</th>
        <th>{{ trans('Answer') }}</th>
        <th class="flex justify-end">{{ trans('Actions') }}</th>
      </tr>
    </thead>
    <tbody v-if="qaReplies.length">
      <tr v-for="(qaReply, index) in qaReplies" :key="qaReply.id">
        <td>{{ index + 1 }}</td>
        <td>{{ qaReply.key }}</td>
        <td>{{ qaReply.value }}</td>
        <td>
          <div class="flex justify-end">
            <div class="dropdown" data-placement="bottom-start">
              <div class="dropdown-toggle">
                <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
              </div>
              <div class="dropdown-content w-56">
                <ul class="dropdown-list">
                  <li class="dropdown-list-item">
                    <a
                      class="dropdown-link"
                      :href="route('user.auto-reply-datasets.edit', qaReply)"
                    >
                      <Icon class="h-5 text-3xl text-slate-400" icon="bx:edit" />
                      {{ trans('Edit') }}
                    </a>
                  </li>
                  <li class="dropdown-list-item">
                    <a
                      class="dropdown-link"
                      @click="deleteRow(route('user.auto-reply-datasets.destroy', qaReply))"
                    >
                      <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                      {{ trans('Delete') }}
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
    <NoDataFound v-else for-table="true" />
  </table>
</template>
