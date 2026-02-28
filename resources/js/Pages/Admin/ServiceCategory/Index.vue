<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { useForm, router } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import moment from 'moment'
import { ref } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'

import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()

const props = defineProps([
  'categories',
  'totalCategories',
  'activeCategories',
  'inActiveCategories',
  'languages'
])

const categoryForm = useForm({
  title: '',
  status: 1,
  icon: null,
  preview: null,
  is_featured: 0
})

const editForm = ref({})

const storeCategory = () => {
  categoryForm.post(route('admin.service-categories.store'), {
    onSuccess: () => {
      categoryForm.reset()
      modalStore.close('createModal')
    }
  })
}

const openEditCategoryDrawer = (category) => {
  editForm.value = { ...category }
  modalStore.open('editModal')
}

const updateCategory = () => {
  if (!(editForm.value.preview instanceof File)) {
    editForm.value.preview = null
  }
  if (!(editForm.value.icon instanceof File)) {
    editForm.value.icon = null
  }
  router.post(
    route('admin.service-categories.update', editForm.value.id),
    {
      _method: 'PATCH',
      category: editForm.value
    },
    {
      onSuccess: () => {
        editForm.value = {}
        modalStore.close('editModal')
      }
    }
  )
}
</script>

<template>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th class="">{{ trans('Name') }}</th>
          <th class="">{{ trans('Slug') }}</th>
          <th class="">{{ trans('Status') }}</th>
          <th class="">{{ trans('Featured') }}</th>
          <th class="">{{ trans('Created At') }}</th>
          <th class="flex justify-end">{{ trans('Action') }}</th>
        </tr>
      </thead>

      <tbody v-if="categories.total > 0">
        <tr v-for="category in categories.data" :key="category.id">
          <td class="flex gap-1">
            <img class="max-h-20" v-lazy="category.icon" alt="" />
            {{ category.title }}
          </td>
          <td class="text-left">
            {{ category.slug }}
          </td>

          <td class="text-left">
            <span class="badge" :class="category.status == 1 ? 'badge-primary' : 'badge-danger'">
              {{ category.status == 1 ? trans('Active') : trans('Inactive') }}
            </span>
          </td>
          <td class="text-left">
            <span
              class="badge"
              :class="category.is_featured == 1 ? 'badge-success' : 'badge-danger'"
            >
              {{ category.is_featured == 1 ? trans('Featured') : trans('Not Featured') }}
            </span>
          </td>
          <td>
            {{ moment(category.created_at).format('D-MMM-Y') }}
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
                      <button @click="openEditCategoryDrawer(category)" class="dropdown-link">
                        <Icon class="w-30 text-lg" icon="bx:edit" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        as="button"
                        class="dropdown-link"
                        @click="deleteRow(route('admin.service-categories.destroy', category.id))"
                      >
                        <Icon class="w-30 text-lg" icon="bx:trash" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
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
  <Paginate :links="categories.links" />

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Create Service Category')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="categoryForm.processing"
    @action="storeCategory"
  >
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Title') }}</label>
      <input v-model="categoryForm.title" type="text" class="input" required />
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Icon') }}</label>
      <input @input="(e) => (categoryForm.icon = e.target.files[0])" type="file" class="input" />
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Preview') }}</label>
      <input
        @input="(e) => (categoryForm.preview = e.target.files[0])"
        type="file"
        class="input"
        required
      />
    </div>

    <div class="mb-2">
      <label class="label mb-2">{{ trans('Is Active?') }}</label>
      <select required v-model="categoryForm.status" class="select">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Draft') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Is Featured?') }}</label>
      <select required v-model="categoryForm.is_featured" class="select">
        <option value="1">{{ trans('Featured') }}</option>
        <option value="0">{{ trans('Not Featured') }}</option>
      </select>
    </div>
  </Modal>

  <Modal
    state="editModal"
    :header-state="true"
    :header-title="trans('Edit Category')"
    :action-btn-text="trans('Update')"
    :action-btn-state="true"
    :action-processing="editForm.processing"
    @action="updateCategory"
  >
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Title') }}</label>
      <input v-model="editForm.title" type="text" class="input" required />
    </div>

    <div class="mb-2">
      <label class="label mb-2">{{ trans('Icon') }}</label>
      <input @input="(e) => (editForm.icon = e.target.files[0])" type="file" class="input" />
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Preview') }}</label>
      <input @input="(e) => (editForm.preview = e.target.files[0])" type="file" class="input" />
    </div>

    <div class="mb-2">
      <label class="label mb-2">{{ trans('Is Active?') }}</label>
      <select required v-model="editForm.status" class="select">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Draft') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Is Featured?') }}</label>
      <select required v-model="editForm.is_featured" class="select">
        <option value="1">{{ trans('Featured') }}</option>
        <option value="0">{{ trans('Not Featured') }}</option>
      </select>
    </div>
  </Modal>
</template>
