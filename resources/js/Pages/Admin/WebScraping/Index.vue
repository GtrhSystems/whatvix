<script setup>
import moment from 'moment'

import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['scrapingRecords'])
const { deleteRow, badgeClass } = sharedComposable()
</script>

<template>
  <div class="table-responsive mt-6 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Date') }}</th>
          <th>
            {{ trans('Title') }}
          </th>
          <th>{{ trans('Category') }}</th>
          <th>{{ trans('Country') }}</th>
          <th>
            {{ trans('City') }}
          </th>
          <th>
            {{ trans('State') }}
          </th>
          <th>{{ trans('Status') }}</th>

          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="scrapingRecords.data.length" class="tbody">
        <tr v-for="(record, index) in scrapingRecords.data" :key="index">
          <td>
            {{ moment(record.created_at).format('DD MMM, YYYY h:mm A') }}
          </td>
          <td>
            {{ record.title }}
          </td>
          <td>
            {{ record?.category?.title || '' }}
          </td>
          <td>
            {{ record.parameters?.country }}
          </td>
          <td>
            {{ record.parameters?.city }}
          </td>
          <td>
            {{ record.parameters?.state }}
          </td>

          <td>
            <span :class="badgeClass(record.status)" class="capitalize">
              {{ record.status }}
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
                      <Link
                        class="dropdown-link"
                        :href="route('admin.web-scraping.show', record.uuid)"
                      >
                        <Icon icon="bx:list-ul" />
                        <span>{{ trans('Logs') }}</span>
                      </Link>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        @click="deleteRow(route('admin.web-scraping.destroy', record.id))"
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

    <Paginate :links="scrapingRecords.links" />
  </div>
</template>
