<script setup>
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['overviews', 'templates'])
const { deleteRow } = sharedComposable()
const filterOptions = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Status',
    value: 'status'
  }
]
</script>

<template>
  <FilterDropdown :options="filterOptions" />
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Template Name') }}
          </th>
          <th>
            {{ trans('Device Name') }}
          </th>
          <th>{{ trans('Type') }}</th>
          <th>{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="templates.data.length" class="tbody">
        <tr v-for="(template, index) in templates.data" :key="index">
          <td>
            <a :href="route('user.whatsapp.templates.show', template)">
              {{ template.name }}
            </a>
          </td>
          <td>
            {{ template.platform?.name ?? 'NA' }}
          </td>
          <td>
            {{ template.type }}
          </td>
          <td>
            <span
              class="badge uppercase"
              :class="
                ['APPROVED', 'active'].includes(template.status) ? 'badge-success' : 'badge-warning'
              "
            >
              {{ template.status }}</span
            >
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <template v-if="template.type !== 'template'">
                      <li class="dropdown-list-item">
                        <a
                          class="dropdown-link"
                          :href="route('user.whatsapp.templates.copy', template.id)"
                        >
                          <Icon class="h-5 text-3xl text-slate-400" icon="bx:copy" />
                          {{ trans('Copy') }}
                        </a>
                      </li>
                      <li class="dropdown-list-item">
                        <a
                          class="dropdown-link"
                          :href="route('user.whatsapp.templates.edit', template.id)"
                        >
                          <Icon class="h-5 text-3xl text-slate-400" icon="bx:edit" />
                          {{ trans('Edit') }}
                        </a>
                      </li>
                    </template>
                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        @click="deleteRow(route('user.whatsapp.templates.destroy', template.id))"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
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
  </div>
  <Paginate :links="templates.links" />
</template>
