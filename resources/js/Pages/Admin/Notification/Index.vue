<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { Link, useForm } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import trans from '@/Composables/transComposable'
import moment from 'moment'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import toast from '@/Composables/toastComposable'
import { onMounted } from 'vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
import { useModalStore } from '@/Store/modalStore'

defineOptions({ layout: AdminLayout })
const { textExcerpt, deleteRow } = sharedComposable()

const props = defineProps(['request', 'notifications', 'type'])

const filterOptions = [
  {
    label: 'Title',
    value: 'title'
  },
  {
    label: 'Comment',
    value: 'comment'
  },
  {
    label: 'User Email',
    value: 'user_email'
  },
  {
    label: 'Status',
    value: 'seen',
    options: [
      {
        label: 'Read',
        value: 1
      },
      {
        label: 'Unread',
        value: 0
      }
    ]
  }
]
const form = useForm({
  email: '',
  title: '',
  description: '',
  url: ''
})

const createNotification = () => {
  form.post(route('admin.notification.store'), {
    onSuccess: () => {
      form.reset()
      toast.success(trans('Notification created successfully'))
      useModalStore().close('addNewNotificationModal')
    }
  })
}
</script>

<template>
  <div class="mt-4 space-y-4">
    <FilterDropdown :options="filterOptions" />

    <!-- Order Table Starts -->
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th class="w-[5%] uppercase">{{ trans('Title') }}</th>
            <th class="w-[15%] uppercase">{{ trans('Comment') }}</th>
            <th class="w-[10%] uppercase">{{ trans('User') }}</th>
            <th class="w-[10%] uppercase">{{ trans('Seen') }}</th>
            <th class="w-[10%] uppercase">{{ trans('Created At') }}</th>
            <th class="w-[5%] !text-right uppercase">
              {{ trans('Actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="notifications.total">
          <tr v-for="notification in notifications.data" :key="notification.id">
            <td class="text-left">
              {{ textExcerpt(notification.title, 80) }}
            </td>
            <td>
              {{ textExcerpt(notification.comment, 50) }}
            </td>
            <td>
              <Link
                v-if="notification.user_id"
                class="text-dark"
                :href="route('admin.users.show', notification.user_id)"
              >
                {{ textExcerpt(notification.user.name, 15) }}
              </Link>
              <span v-else>{{ trans('None') }}</span>
            </td>

            <td>
              <span
                class="badge"
                :class="notification.seen == 1 ? 'badge-success' : 'badge-danger'"
              >
                {{ notification.seen == 1 ? 'Read' : 'Unread' }}
              </span>
            </td>

            <td class="text-center">
              {{ moment(notification.created_at).format('DD-MMM-YYYY') }}
            </td>
            <td>
              <div class="flex justify-end">
                <div class="dropdown" data-placement="bottom-start">
                  <div class="dropdown-toggle">
                    <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                  </div>
                  <div class="dropdown-content w-40">
                    <ul class="dropdown-list">
                      <li class="dropdown-list-item">
                        <a
                          class="dropdown-link delete-confirm"
                          href="javascript:void(0)"
                          @click="deleteRow(route('admin.notification.destroy', notification.id))"
                        >
                          <Icon class="w-30 text-lg" icon="bx:trash" />
                          <span>{{ trans('Delete') }}</span>
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
    </div>

    <Paginate :links="notifications.links" />
  </div>

  <Modal
    state="addNewNotificationModal"
    :header-state="true"
    header-title="Send Notification"
    :action-btn-state="true"
    :action-btn-text="trans('Create Notification')"
    :action-processing="form.processing"
    @action="createNotification"
  >
    <div class="form-group">
      <label>{{ trans('Receive Email') }}</label>
      <input v-model="form.email" type="email" name="email" class="input" required />
    </div>
    <div class="form-group">
      <label>{{ trans('Title') }}</label>
      <input v-model="form.title" type="text" name="title" class="input" required maxlength="100" />
    </div>
    <div class="form-group">
      <label>{{ trans('Description') }}</label>
      <textarea
        v-model="form.description"
        class="textarea"
        required
        name="description"
        maxlength="200"
      ></textarea>
    </div>
    <div class="form-group">
      <label>{{ trans('Action Link') }}</label>
      <input v-model="form.url" type="url" name="url" class="input" required maxlength="100" />
    </div>
  </Modal>
</template>
