<script setup>
import { Icon } from '@iconify/vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable.js'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'
import moment from 'moment'

const modal = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['warmers'])
const { deleteRow } = sharedComposable()

const createFrom = useForm({
  title: ''
})

const createWarmer = () => {
  createFrom.post(route('user.whatsapp-web.warmer.store'), {
    onSuccess: () => {
      modal.close('warmerCreate')
      createFrom.reset()
    }
  })
}

const editFrom = useForm({
  id: null,
  title: ''
})

const warmerEdit = () => {
  editFrom.put(route('user.whatsapp-web.warmer.update', { warmer: editFrom.id }), {
    onSuccess: () => modal.close('warmerEdit')
  })
}

const setEditFormValue = (warmer) => {
  editFrom.id = warmer.id
  editFrom.title = warmer.title
  modal.open('warmerEdit')
}
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th class="w-[40%]">
            {{ trans('Title') }}
          </th>
          <th class="w-[50%] !text-right">
            {{ trans('Date') }}
          </th>

          <th class="w-[10%] !text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="warmers.data.length" class="tbody">
        <tr v-for="(warmer, index) in warmers.data" :key="index">
          <td>
            <Link class="underline" :href="route('user.whatsapp-web.warmer.show', warmer.id)">
              <span>{{ warmer.title }}</span>
            </Link>
          </td>
          <td class="!text-right">
            {{ moment(warmer.created_at).format('DD MMM, YYYY h:mm A') }}
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
                        :href="route('user.whatsapp-web.warmer.show', warmer.id)"
                      >
                        <Icon icon="bx:show" />
                        <span>{{ trans('Manage') }}</span>
                      </Link>
                    </li>
                    <li class="dropdown-list-item">
                      <button type="button" class="dropdown-link" @click="setEditFormValue(warmer)">
                        <Icon icon="bx:bxs-edit-alt" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        type="button"
                        class="dropdown-link delete-confirm"
                        @click="deleteRow(route('user.whatsapp-web.warmer.destroy', warmer.id))"
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
    <div class="w-full">
      <Paginate :links="warmers.links" />
    </div>
  </div>

  <Modal state="warmerCreate">
    <form @submit.prevent="createWarmer">
      <div class="mb-2">
        <label>{{ trans('Title') }}</label>
        <input type="text" v-model="createFrom.title" class="input" placeholder="Title" required />
      </div>
      <div class="mt-2 text-end">
        <SpinnerBtn
          classes="btn btn-primary"
          :processing="createFrom.processing"
          :btn-text="trans('Create')"
        />
      </div>
    </form>
  </Modal>

  <Modal state="warmerEdit">
    <form @submit.prevent="warmerEdit">
      <div class="mb-2">
        <label>{{ trans('Title') }}</label>
        <input type="text" v-model="editFrom.title" class="input" />
      </div>
      <div class="mt-2 text-end">
        <SpinnerBtn
          classes="btn btn-primary"
          :processing="editFrom.processing"
          :btn-text="trans('Save Changes')"
        />
      </div>
    </form>
  </Modal>
</template>
