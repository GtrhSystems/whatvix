<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import moment from 'moment'
import { useForm, router } from '@inertiajs/vue3'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })

const props = defineProps([
  'menus',
  'languages',
  'totalMenus',
  'totalActiveMenus',
  'totalDraftMenus'
])
const { deleteRow } = sharedComposable()

const form = useForm({
  name: null,
  position: null,
  language: 'en',
  status: 0
})

const edit = useForm({
  name: null,
  position: null,
  language: 'en',
  status: null,
  id: null
})

const openEditFaqDrawer = (menu) => {
  edit.name = menu.name
  edit.position = menu.position
  edit.language = menu.lang
  edit.status = menu.status
  edit.id = menu.id
  modalStore.open('editModal')
}

function storeMenu() {
  const url = route('admin.menu.store')
  router.post(url, form, {
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}

function updateMenu() {
  const url = route('admin.menu.update', edit.id)

  router.patch(url, edit, {
    onSuccess: () => {
      modalStore.close('editModal')
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Menu Name') }}</th>
            <th>{{ trans('Position') }}</th>
            <th>{{ trans('Language') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Last Update') }}</th>
            <th class="!text-right">{{ trans('Action') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="menu in menus" :key="menu.id">
            <td class="text-left">
              <Link :href="route('admin.menu.show', menu.id)">{{ menu.name }}</Link>
            </td>
            <td class="text-left">
              {{ menu.position }}
            </td>
            <td class="text-center">
              {{ menu.lang }}
            </td>
            <td class="text-left">
              <span class="badge" :class="menu.status == 1 ? 'badge-success' : 'badge-danger'">
                {{ menu.status == 1 ? trans('Active') : trans('Draft') }}
              </span>
            </td>
            <td>
              {{ moment(menu.updated_at).fromNow() }}
            </td>
            <td>
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="text-2xl" icon="bx:dots-horizontal-rounded" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <Link :href="route('admin.menu.show', menu.id)" class="dropdown-link">
                        <Icon icon="bx:grid" />
                        <span>{{ trans('Manage') }}</span>
                      </Link>
                    </li>
                    <li class="dropdown-list-item">
                      <button @click="openEditFaqDrawer(menu)" class="dropdown-link">
                        <Icon icon="fe:edit" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link"
                        @click="deleteRow(route('admin.menu.destroy', menu.id))"
                      >
                        <Icon icon="fe:trash" />
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
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Add New Menu')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="form.processing"
    @action="storeMenu"
  >
    <div>
      <label class="label label-required mb-1">{{ trans('Menu Name') }}</label>
      <input
        type="text"
        v-model="form.name"
        name="name"
        class="input"
        required
        placeholder="Example"
      />
    </div>
    <div>
      <label class="label label-required mb-1">{{ trans('Select Menu Position') }}</label>
      <select class="input" name="position" v-model="form.position">
        <option value="main-menu">{{ trans('Main Menu') }}</option>
        <option value="footer-left">
          {{ trans('Footer Left') }}
        </option>
        <option value="footer-right">
          {{ trans('Footer right') }}
        </option>
        <option value="footer-center">
          {{ trans('Footer Center') }}
        </option>
      </select>
    </div>
    <div>
      <label class="label label-required mb-1">{{ trans('Select Language') }}</label>
      <select class="input" name="language" v-model="form.language">
        <option
          v-for="(language, languageKey) in languages"
          :key="language.id"
          :value="languageKey"
        >
          {{ language }}
        </option>
      </select>
    </div>
    <div>
      <label class="label label-required mb-1">{{ trans('Menu Status') }}</label>
      <select class="select" name="status" v-model="form.status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Draft') }}</option>
      </select>
    </div>
  </Modal>

  <Modal
    state="editModal"
    :header-state="true"
    :header-title="trans('Edit Menu')"
    :action-btn-text="trans('Update')"
    :action-btn-state="true"
    :action-processing="edit.processing"
    @action="updateMenu"
  >
    <div>
      <label class="label label-required">{{ trans('Menu Name') }}</label>
      <input
        v-model="edit.name"
        type="text"
        name="name"
        class="input"
        required
        placeholder="Name"
      />
    </div>
    <div>
      <label class="label label-required">{{ trans('Select Menu Position') }}</label>
      <select class="input" name="position" v-model="edit.position">
        <option value="main-menu">{{ trans('Main Menu') }}</option>
        <option value="footer-left">
          {{ trans('Footer Left') }}
        </option>
        <option value="footer-right">
          {{ trans('Footer right') }}
        </option>
        <option value="footer-center">
          {{ trans('Footer Center') }}
        </option>
      </select>
    </div>
    <div>
      <label class="label label-required">{{ trans('Select Language') }}</label>
      <select name="language" class="input" v-model="edit.language" required>
        <option
          v-for="(language, languageKey) in languages"
          :key="language.id"
          :value="languageKey"
        >
          {{ language }}
        </option>
      </select>
    </div>
    <div c>
      <label class="label label-required">{{ trans('Menu Status') }}</label>
      <select class="select" name="status" v-model="edit.status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Draft') }}</option>
      </select>
    </div>
  </Modal>
</template>