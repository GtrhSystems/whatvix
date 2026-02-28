<script setup>
import { useForm } from '@inertiajs/vue3'
import FloatingDropdown from '@/Components/Dashboard/FloatingDropdown.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import NotificationRing from '@/Components/Chats/NotificationRing.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['apps', 'platforms', 'authKey'])
const { deleteRow } = sharedComposable()
const modal = useModalStore()

const form = useForm({
  name: '',
  site_link: '',
  platform_id: ''
})
const submit = () => {
  form.post(route('user.whatsapp-web.apps.store'), {
    onSuccess: () => {
      form.reset()
      modal.close()
    }
  })
}
</script>

<template>
  <NotificationRing module="whatsapp-web" />
  <Modal state="appModal" :header-state="true" header-title="Create New App">
    <form @submit.prevent="submit">
      <div>
        <label class="label mt-2">{{ trans('Select Device') }}</label>
        <select class="select" v-model="form.platform_id">
          <option value="">{{ trans('Select Device') }}</option>
          <option :value="device.id" v-for="device in platforms" :key="device.id">
            {{ device.name }}
          </option>
        </select>
        <small class="text-gray-600">{{
          trans('User Will Receive Message From The Selected Number')
        }}</small>
        <br />
        <small class="text-red-600" v-if="form.errors.platform_id">{{
          form.errors.platform_id
        }}</small>
      </div>
      <div class="my-3">
        <label class="label mt-2">{{ trans('App Name') }}</label>
        <input type="text" class="input" v-model="form.name" />
        <small class="text-red-600" v-if="form.errors.name">{{ form.errors.name }}</small>
      </div>
      <div>
        <label class="label mt-2">{{ trans('Web Link') }}</label>
        <input type="url" class="input" v-model="form.site_link" />
        <small class="text-red-600" v-if="form.errors.site_link">{{ form.errors.site_link }}</small>
      </div>

      <div class="mt-4 flex justify-end">
        <SpinnerBtn :processing="form.processing" :btn-text="trans('Create App')" />
      </div>
    </form>
  </Modal>

  <div class="mt-4 flex w-full flex-col gap-4">
    <div
      class="my-6 grid grid-flow-row grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3"
      v-if="apps?.data.length"
    >
      <div v-for="(app, index) in apps.data" :key="index" class="card card-body">
        <div class="flex justify-between">
          <h5 class="card-title mb-3 uppercase text-gray-400 dark:text-gray-50">
            {{ app?.name }}
          </h5>
          <FloatingDropdown btn-type="icon" icon-name="bx:dots-vertical-rounded" btnClass="">
            <ul class="dropdown-list">
              <li class="dropdown-list-item">
                <Link
                  :href="route('user.whatsapp-web.apps.logs', app.uuid)"
                  class="dropdown-link gap-4"
                >
                  <Icon icon="bx:detail" />
                  {{ trans('Logs') }}
                </Link>
              </li>
              <li class="dropdown-list-item">
                <button
                  type="button"
                  @click="deleteRow(route('user.whatsapp-web.apps.destroy', app.uuid))"
                  class="dropdown-link gap-4"
                >
                  <Icon icon="bx:trash" />

                  {{ trans('Delete') }}
                </button>
              </li>
            </ul>
          </FloatingDropdown>
        </div>

        <div class="flex flex-col gap-2 text-sm">
          <div class="flex gap-3">
            <p class="w-1/3">{{ trans('Platform') }}</p>
            :
            <span>{{ app.platform?.name }}</span>
          </div>
          <div class="flex gap-3">
            <p class="w-1/3">{{ trans('Endpoint') }}</p>
            :
            <span class="truncate">{{ app.site_link }}</span>
          </div>
        </div>

        <Link
          :href="route('user.whatsapp-web.apps.show', app.uuid)"
          class="mt-4 flex items-center text-sm text-primary-600 hover:underline"
        >
          <span class="font-semibold">{{ trans('Integration') }}</span>
          <Icon class="mt-1 text-xl" icon="bx:right-arrow-alt" />
        </Link>
      </div>
    </div>
    <NoDataFound v-else />
    <div class="w-full">
      <Paginate :links="apps.links" />
    </div>
  </div>
</template>
