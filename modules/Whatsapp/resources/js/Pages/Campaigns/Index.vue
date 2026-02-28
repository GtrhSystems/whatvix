<script setup>
import moment from 'moment'
import momentTimezone from 'moment-timezone'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
defineOptions({ layout: UserLayout })
const props = defineProps(['campaigns','systemTimezone'])
const { deleteRow } = sharedComposable()
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
  <FilterDropdown :options="filterOptions" />
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>
            {{ trans('Campaign') }}
          </th>
          <th>{{ trans('Group') }}</th>
          <th>
            {{ trans('Message Type') }}
          </th>
          <th>{{ trans('Template') }}</th>
          <th>{{ trans('Schedule') }}</th>
          <th>{{ trans('Status') }}</th>
          <th>{{ trans('Created At') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="campaigns.data.length" class="tbody">
        <tr v-for="(campaign, index) in campaigns.data" :key="index">
          <td>
            {{ campaign.name }}
          </td>
          <td>
            {{ campaign?.group?.name }}
          </td>
          <td>
            {{ campaign.message_type }}
          </td>
          <td>
            {{ campaign?.template?.name ?? '-' }}
          </td>
          <td>
            <div class="flex flex-col gap-1">
              <span
                class="font-base text-[11px]"
                v-if="campaign.send_type == 'scheduled' && campaign.schedule_at"
              >
                <span class="font-bold">
                  {{ campaign.timezone }}
                </span>
                <br />
                 {{  campaign.schedule_at != null ? 
                        momentTimezone.tz(campaign.schedule_at, systemTimezone) 
                        .tz(campaign.timezone)                             
                        .format('DD MMM YYYY hh:mm A')  
                      : 'N/A' 
                  }}
               
              </span>
              <span v-else>-</span>
            </div>
          </td>
          <td>
            <span
              class="badge capitalize"
              :class="{
                'badge-info': campaign.status === 'draft',
                'badge-secondary': campaign.status === 'pending',
                'badge-primary': campaign.status === 'scheduled',
                'badge-success': campaign.status === 'send'
              }"
            >
              {{ campaign.status }}
            </span>
          </td>
          <td>
            {{ moment(campaign.created_at).format('dd-MM-YYYY hh:mm a') }}
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <template v-if="campaign.status == 'send'">
                      <li class="dropdown-list-item">
                        <a
                          class="dropdown-link"
                          :href="route('user.whatsapp.campaigns.show', campaign.id)"
                        >
                          <Icon icon="bx:list-ul" />
                          <span>{{ trans('Logs') }}</span>
                        </a>
                      </li>
                    </template>
                    <template v-else>
                      <li class="dropdown-list-item" v-if="campaign.status == 'scheduled'">
                        <a
                          class="dropdown-link"
                          :href="route('user.whatsapp.campaigns.send', campaign.id)"
                        >
                          <Icon icon="bx:send" />
                          <span>{{ trans('Send Now') }}</span>
                        </a>
                      </li>

                      <li class="dropdown-list-item" v-if="campaign.status != 'send'">
                        <a
                          class="dropdown-link"
                          :href="route('user.whatsapp.campaigns.edit', campaign.id)"
                        >
                          <Icon icon="bx:edit" />
                          <span>{{ trans('Edit') }}</span>
                        </a>
                      </li>
                    </template>

                    <li class="dropdown-list-item">
                      <a
                        class="dropdown-link"
                        :href="route('user.whatsapp.campaigns.copy', campaign.id)"
                      >
                        <Icon icon="bx:copy" />
                        <span>{{ trans('Copy') }}</span>
                      </a>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        href="#"
                        @click="deleteRow(route('user.whatsapp.campaigns.destroy', campaign.id))"
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

    <Paginate :links="campaigns.links" />
  </div>
</template>
