<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import moment from 'moment'
import { ref } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })
const { deleteRow } = sharedComposable()
const props = defineProps(['tags', 'languages'])

const tagForm = useForm({
  title: '',
  status: 'active',
  language: ''
})

const editForm = ref({})

const storeTag = () => {
  tagForm.post(route('admin.tag.store'), {
    onSuccess: () => {
      tagForm.reset()
      modalStore.close('createModal')
    }
  })
}

const openEditModal = (tag) => {
  editForm.value = { ...tag }
  modalStore.open('editModal')
}

const updateTag = () => {
  router.patch(route('admin.tag.update', editForm.value.id), editForm.value, {
    onSuccess: () => {
      editForm.value = {}
      modalStore.close('editModal')
    }
  })
}
</script>

<template>
  <div class="card">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th class="w-2/12">{{ trans('Name') }}</th>
            <th class="w-2/12">{{ trans('Slug') }}</th>
            <th class="w-2/12">
              <p class="text-center">{{ trans('Uses for blog') }}</p>
            </th>
            <th class="w-2/12">{{ trans('Status') }}</th>
            <th class="w-2/12">{{ trans('Created At') }}</th>
            <th class="mr-auto w-2/12">
              <p class="text-end">{{ trans('Action') }}</p>
            </th>
          </tr>
        </thead>
        <tbody v-if="tags.total">
          <tr v-for="tag in tags.data" :key="tag.id">
            <td class="text-left">
              {{ tag.title }}
            </td>
            <td class="text-left">
              {{ tag.slug }}
            </td>
            <td>
              <p class="text-center">{{ tag.post_categories_count ?? 0 }}</p>
            </td>
            <td class="text-left">
              <span class="badge" :class="[tag.status == 1 ? 'badge-primary' : 'badge-danger']">
                {{ tag.status == 1 ? trans('Active') : trans('Draft') }}
              </span>
            </td>
            <td>
              {{ moment(tag.created_at).format('D-MMM-Y') }}
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
                        <button @click="openEditModal(tag)" class="dropdown-link">
                          <Icon class="h-6 text-slate-400" icon="material-symbols:edit-outline" />
                          <span>{{ trans('Edit') }}</span>
                        </button>
                      </li>

                      <li class="dropdown-list-item">
                        <button
                          as="button"
                          class="dropdown-link"
                          @click="deleteRow('/admin/tag/' + tag.id)"
                        >
                          <Icon class="h-6" icon="material-symbols:delete-outline" />
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
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    header-title="Add New Tag"
    :action-btn-state="true"
    action-btn-text="Create"
    :action-processing="tagForm.processing"
    @action="storeTag"
  >
    <div class="mb-2">
      <label>{{ trans('Title') }}</label>
      <input v-model="tagForm.title" type="text" name="title" class="input" required="" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="tagForm.status" class="select" name="status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Deactive') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Language') }}</label>
      <select v-model="tagForm.language" class="select" name="language">
        <template v-for="(language, key) in languages" :key="key">
          <option :value="key">{{ language }}</option>
        </template>
      </select>
    </div>
  </Modal>

  <Modal
    state="editModal"
    header-title="Edit Tag"
    action-btn-text="Update"
    :header-state="true"
    :action-btn-state="true"
    :action-processing="editForm.processing"
    @action="updateTag"
  >
    <div class="mb-2">
      <label>{{ trans('Title') }}</label>
      <input v-model="editForm.title" type="text" name="title" id="title" class="input" required />
    </div>

    <div class="mb-2">
      <label>{{ trans('Status') }}</label>
      <select v-model="editForm.status" class="select" name="status" id="status">
        <option value="1">{{ trans('Active') }}</option>
        <option value="0">{{ trans('Deactive') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Language') }}</label>
      <select v-model="editForm.lang" class="select" name="language" id="language">
        <template v-for="(language, key) in languages" :key="key">
          <option :value="key">{{ language }}</option>
        </template>
      </select>
    </div>
  </Modal>
</template>
