<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import sharedComposable from '@/Composables/sharedComposable'
import Pagination from '@/Components/Dashboard/Paginate.vue'
import { Link } from '@inertiajs/vue3'
import trans from '@/Composables/transComposable'

defineOptions({ layout: AdminLayout })
const { textExcerpt, deleteRow } = sharedComposable()

const props = defineProps(['posts'])
</script>

<template>
  <div class="space-y-6">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Name') }}</th>
            <th>{{ trans('Position') }}</th>

            <th class="text-right">{{ trans('Status') }}</th>
            <th class="flex justify-end">{{ trans('Action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="post in posts.data" :key="post.id">
            <td class="flex items-center gap-2">
              <img v-lazy="post.preview?.value" class="avatar rounded-square mr-3" />
              {{ textExcerpt(post.title, 50) }}
            </td>
            <td class="text-left">
              {{ textExcerpt(post.slug, 50) }}
            </td>

            <td class="text-right">
              <span class="badge" :class="post.status == 1 ? 'badge-success' : 'badge-danger'">
                {{ post.status == 1 ? trans('Active') : trans('Draft') }}
              </span>
            </td>

            <td>
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link :href="route('admin.team.edit', post.id)" class="dropdown-link">
                        <Icon class="w-30 text-lg" icon="bx:edit" />
                        <span>{{ trans('Edit') }}</span>
                      </Link>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        type="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.team.destroy', post.id))"
                      >
                        <Icon class="w-30 text-lg" icon="bx:trash" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="posts.data.length != 0" :links="posts.links" />
  </div>
</template>
