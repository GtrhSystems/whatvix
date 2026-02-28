<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { Link, useForm, router } from '@inertiajs/vue3'
import moment from 'moment'
import { ref } from 'vue'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()

const props = defineProps(['categories', 'languages'])

const categoryForm = useForm({
  title: '',
  status: 1,
  language: 'en',
  type: 'blog_category'
})

const editForm = ref({})

const storeCategory = () => {
  categoryForm.post(route('admin.category.store'), {
    onSuccess: () => {
      categoryForm.reset()
      modalStore.close('createModal')
    }
  })
}

const openEditCategoryModal = (category) => {
  editForm.value = { ...category }
  modalStore.open('editModal')
}

const updateCategory = () => {
  router.patch(route('admin.category.update', editForm.value.id), editForm.value, {
    onSuccess: () => {
      editForm.value = {}
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
            <th>{{ trans('Name') }}</th>
            <th>{{ trans('Slug') }}</th>
            <th class="text-center">{{ trans('Language') }}</th>
            <th>{{ trans('Status') }}</th>
            <th>{{ trans('Created At') }}</th>
            <th class="flex justify-end">
              {{ trans('Action') }}
            </th>
          </tr>
        </thead>

        <tbody v-if="categories.total">
          <tr v-for="category in categories.data" :key="category.id">
            <td class="text-left">
              {{ category.title }}
            </td>
            <td class="text-left">
              {{ category.slug }}
            </td>
            <td class="text-center">
              {{ category.lang }}
            </td>
            <td class="text-left">
              <span class="badge" :class="category.status == 1 ? 'badge-success' : 'badge-danger'">
                {{ category.status == 1 ? trans('Active') : trans('Draft') }}
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
                        <button @click="openEditCategoryModal(category)" class="dropdown-link">
                          <Icon class="h-6 text-slate-400" icon="material-symbols:edit-outline" />
                          <span>{{ trans('Edit') }}</span>
                        </button>
                      </li>

                      <li class="dropdown-list-item">
                        <Link
                          as="button"
                          class="dropdown-link"
                          @click="deleteRow('/admin/category/' + category.id)"
                        >
                          <Icon class="h-6" icon="material-symbols:delete-outline" />
                          <span>{{ trans('Delete') }}</span>
                        </Link>
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
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Create Category')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="categoryForm.processing"
    @action="storeCategory"
  >
    <div class="mb-2">
      <label>{{ trans('Title') }}</label>
      <input v-model="categoryForm.title" type="text" name="title" class="input" required />
    </div>
    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select required v-model="categoryForm.status" class="select" name="status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Deactivate') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Language') }}</label>
      <select required v-model="categoryForm.language" class="select" name="language">
        <template v-for="(language, key) in languages" :key="key">
          <option :value="key">{{ language }}</option>
        </template>
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
      <label>{{ trans('Title') }}</label>
      <input v-model="editForm.title" type="text" name="title" class="input" required />
    </div>

    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="editForm.status" class="select" name="status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Draft') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Language') }}</label>
      <select v-model="editForm.lang" class="select" name="language">
        <template v-for="(language, key) in languages" :key="key">
          <option :value="key">{{ language }}</option>
        </template>
      </select>
    </div>
  </Modal>
</template>
