<script setup>
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'

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
        <th class="w-[5%]">{{ trans('#') }}</th>
        <th class="w-full">{{ trans('Title') }}</th>
        <th class="w-[10%]">{{ trans('Items') }}</th>
        <th class="flex justify-end">{{ trans('Actions') }}</th>
      </tr>
    </thead>
    <tbody v-if="qaReplies.total" class="text-start">
      <tr v-for="(qaReplies, index) in qaReplies.data" :key="qaReplies.id">
        <td>{{ index + 1 }}</td>
        <td>{{ qaReplies.title }}</td>
        <td>{{ qaReplies.items_count }}</td>
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
                      :href="route('user.qareply.qareplies.edit', qaReplies)"
                    >
                      <Icon class="h-5 text-3xl text-slate-400" icon="bx:edit" />
                      {{ trans('Edit') }}
                    </Link>
                  </li>
                  <li class="dropdown-list-item">
                    <a
                      class="dropdown-link"
                      @click="deleteRow(route('user.qareply.qareplies.destroy', qaReplies))"
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
  <Paginate :links="qaReplies.links" />
</template>
