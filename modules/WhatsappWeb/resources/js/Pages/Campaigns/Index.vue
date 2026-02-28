<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NotificationRing from '@/Components/Chats/NotificationRing.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import moment from 'moment'
import momentTimezone from 'moment-timezone'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['campaigns', 'systemTimezone'])
const { deleteRow, badgeClass } = sharedComposable()
const filterOptions = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Message Type',
    value: 'message_type',
    options: [
      {
        label: 'Text',
        value: 'text'
      },
      {
        label: 'template',
        value: 'template'
      }
    ]
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Send',
        value: 'send'
      },
      {
        label: 'Draft',
        value: 'draft'
      },
      {
        label: 'Pending',
        value: 'pending'
      },
      {
        label: 'Scheduled',
        value: 'scheduled'
      }
    ]
  }
]
</script>

<template>
  <NotificationRing module="whatsapp-web" />
  <FilterDropdown :options="filterOptions" />
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Title') }}
          </th>
          <th>{{ trans('Platform') }}</th>
          <th>{{ trans('Group') }}</th>
          <th>{{ trans('Message Type') }}</th>
          <th>{{ trans('Template') }}</th>
          <th class="!text-center">{{ trans('Status') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="campaigns.data.length" class="tbody">
        <tr v-for="(campaign, index) in campaigns.data" :key="index">
          <td>
            <Link :href="route('user.whatsapp-web.campaigns.show', campaign)">
              {{ campaign.name }}
            </Link>
          </td>
          <td>
            {{ campaign.platform?.name ?? trans('N/A') }}
          </td>
          <td>
            {{ campaign.group?.name ?? trans('N/A') }}
          </td>
          <td>
            <span class="uppercase"> {{ campaign.message_type }}</span>
          </td>
          <td>
            <span :class="badgeClass(campaign.status)" v-if="campaign.template_id">
              {{ campaign.template?.name ?? trans('N/A') }}
            </span>
            <span v-else class="uppercase">N/A</span>
          </td>
          <td class="!text-right">
            <div class="flex flex-col items-center justify-center gap-1">
              <span class="capitalize" :class="badgeClass(campaign.status)">
                {{ campaign.status }}
              </span>

              <div
                class="font-base flex flex-col items-center text-[11px]"
                v-if="campaign.status == 'scheduled'"
              >
                <div class="flex items-center">
                  <Icon class="mr-1 mt-px h-3 w-3 text-slate-400" icon="fe:clock" />

                  {{
                    campaign.schedule_at != null
                      ? momentTimezone
                          .tz(campaign.schedule_at, systemTimezone)
                          .tz(campaign.timezone)
                          .format('DD MMM YYYY hh:mm A')
                      : 'N/A'
                  }}
                </div>
                <span
                  v-if="campaign.schedule_at"
                  class="-mt-1 italic text-gray-600 dark:text-gray-500"
                >
                  ({{
                    momentTimezone
                      .tz(campaign.schedule_at, systemTimezone)
                      .tz(campaign.timezone)
                      .fromNow()
                  }})
                </span>
              </div>
            </div>
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item" v-if="campaign.status == 'scheduled'">
                      <a
                        class="dropdown-link"
                        :href="route('user.whatsapp-web.campaigns.edit', campaign.id)"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:send" />
                        {{ trans('Send Now') }}
                      </a>
                    </li>
                    <li class="dropdown-list-item" v-else-if="campaign.status == 'send'">
                      <Link
                        class="dropdown-link"
                        :href="route('user.whatsapp-web.campaigns.show', campaign.id)"
                      >
                        <Icon class="h-5 text-3xl text-slate-400" icon="bx:list-ul" />
                        {{ trans('Logs') }}
                      </Link>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        href="#"
                        @click="
                          deleteRow(route('user.whatsapp-web.campaigns.destroy', campaign.id))
                        "
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
  <Paginate :links="campaigns.links" />
</template>
