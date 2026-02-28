<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
import toast from '@/Composables/toastComposable'
import trans from '@/Composables/transComposable'
import { ref } from 'vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()
defineOptions({ layout: AdminLayout })

const { deleteRow } = sharedComposable()

const props = defineProps(['languages'])

const form = useForm({
  name: ''
})
const editForm = ref({})
const isProcessing = ref(false)
const storeLanguage = () => {
  form.post(route('admin.ai-language.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}
const updateLanguage = () => {
  isProcessing.value = true
  router.patch(route('admin.ai-language.update', editForm.value.id), editForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      editForm.value = {}
      modalStore.close('editModal')
    },
    onFinish: () => (isProcessing.value = false)
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th class="col-2">{{ trans('Name') }}</th>
            <th class="flex justify-end">
              {{ trans('Action') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="language in languages" :key="language.code">
            <td class="text-left">
              {{ language.name }}
            </td>
            <td class="flex justify-end gap-3">
              <button
                type="button"
                class="btn btn-dark"
                @click="
                  () => {
                    editForm = { ...language }
                    modalStore.open('editModal')
                  }
                "
              >
                <Icon icon="fe:edit" />
              </button>
              <button
                type="button"
                class="btn btn-danger"
                @click="deleteRow(route('admin.ai-language.destroy', language.id))"
              >
                <Icon icon="fe:trash" />
              </button>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-if="languages.length < 1" for-table="true" />
      </table>
    </div>
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    header-title="Add New Language"
    :action-btn-state="true"
    action-btn-text="Create"
    :action-processing="form.processing"
    @action="storeLanguage"
  >
    <div class="mb-2">
      <label>{{ trans('Name') }}</label>
      <input
        v-model="form.name"
        type="text"
        name="name"
        class="input"
        required
        placeholder="English"
      />
    </div>
  </Modal>

  <Modal
    state="editModal"
    :header-state="true"
    header-title="Edit Language"
    :action-btn-state="true"
    action-btn-text="Update"
    :action-processing="form.processing"
    @action="updateLanguage"
  >
    <div class="mb-2">
      <label class="label mb-2">{{ trans('Name') }}</label>
      <input v-model="editForm.name" type="text" name="name" class="input" required />
    </div>
  </Modal>
</template>
