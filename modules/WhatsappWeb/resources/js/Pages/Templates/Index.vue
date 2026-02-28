<script setup>
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['overviews', 'templates'])
const { deleteRow, badgeClass } = sharedComposable()
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
  <FilterDropdown :options="filterOptions" :title="trans('Templates')" />
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th class="w-1/3">
            {{ trans('Template Name') }}
          </th>

          <th>{{ trans('Type') }}</th>
          <th class="!text-right">{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="templates.data.length" class="tbody">
        <tr v-for="(template, index) in templates.data" :key="index">
          <td>
            <Link :href="route('user.whatsapp-web.templates.show', template)">
              {{ template.name }}
            </Link>
          </td>

          <td>
            <span class="capitalize">{{ template.type }}</span>
          </td>
          <td class="!text-right">
            <span class="capitalize" :class="badgeClass(template.status)">
              {{ template.status }}
            </span>
          </td>
          <td class="!text-right">
            <div class="dropdown" data-placement="bottom-start">
              <div class="dropdown-toggle">
                <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
              </div>
              <div class="dropdown-content w-56">
                <ul class="dropdown-list">
                  <li class="dropdown-list-item">
                    <a
                      class="dropdown-link"
                      :href="
                        route('user.whatsapp-web.campaigns.create', {
                          template_id: template.id
                        })
                      "
                    >
                      <Icon class="h-5 text-3xl text-slate-400" icon="bx:plus" />
                      {{ trans('Create Campaign') }}
                    </a>
                  </li>
                  <li class="dropdown-list-item">
                    <a
                      class="dropdown-link"
                      :href="route('user.whatsapp-web.templates.edit', template.id)"
                    >
                      <Icon class="h-5 text-3xl text-slate-400" icon="bx:edit" />
                      {{ trans('Edit') }}
                    </a>
                  </li>
                  <li class="dropdown-list-item">
                    <button
                      class="dropdown-link delete-confirm"
                      @click="deleteRow(route('user.whatsapp-web.templates.destroy', template.id))"
                    >
                      <Icon class="h-5 text-3xl text-slate-400" icon="bx:trash" />
                      {{ trans('Delete') }}
                    </button>
                  </li>
                </ul>
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
