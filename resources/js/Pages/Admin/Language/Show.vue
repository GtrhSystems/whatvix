<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import { useForm, router } from '@inertiajs/vue3'

import { ref } from 'vue'
import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })

const props = defineProps(['posts', 'id', 'buttons', 'segments'])

const form = useForm({
  key: '',
  value: '',
  id: props.id
})

const createKey = () => {
  form.post('/admin/language/addkey', {
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}

const isProcessing = ref(false)

const updateLanguage = () => {
  isProcessing.value = true
  router.patch(
    route('admin.language.update', props.id),
    {
      values: props.posts
    },
    {
      onSuccess: () => {
        form.reset()
        isProcessing.value = false
        modalStore.close('editModal')
      }
    }
  )
}
</script>
<template>
  <form @submit.prevent="updateLanguage" method="post">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th class="col-6">{{ trans('Translation Key') }}</th>
            <th class="col-6">{{ trans('Translated Value') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(value, key) in posts" :key="key">
            <td>
              {{ key }}
            </td>
            <td>
              <input type="text" class="input" v-model="posts[key]" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      <SpinnerBtn :btn-text="trans('Save Changes')" :processing="isProcessing" />
    </div>
  </form>

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Add New Key')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="form.processing"
    @action="createKey"
  >
    <div class="mb-2">
      <label>{{ trans('Key') }}</label>
      <input type="text" name="key" v-model="form.key" class="input" required />
    </div>
    <div class="mb-2">
      <label>{{ trans('Value') }}</label>
      <input type="text" name="value" v-model="form.value" class="input" required />
    </div>
  </Modal>
</template>
