<script setup>
import { computed } from 'vue'
import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import SelectField from '@/Components/Forms/SelectField.vue'
import { useModalStore } from '@/Store/modalStore'
import InputField from '@/Components/Forms/InputField.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

const modalStore = useModalStore()

const { deleteRow } = sharedComposable()

defineOptions({ layout: AdminLayout })

const props = defineProps(['models', 'providers'])

const isEditing = computed(() => !!form.id)

const setEditForm = (item) => {
  form.id = item.id
  form.provider = item.provider
  form.name = item.name
  form.code = item.code
  form.max_token = item.max_token
  form.status = item.status
  modalStore.open('createModal')
}

const modelData = computed(() => props.models.data)
const hasModels = computed(() => modelData.value.length > 0)

const form = useForm({
  id: '',
  provider: '',
  name: '',
  code: '',
  max_token: 0,
  status: 'active'
})

const submit = () => {
  if (form.id) {
    form.put(route('admin.ai-models.update', form.id), {
      onSuccess: () => {
        form.reset()
        modalStore.close('createModal')
      }
    })
    return
  }

  form.post(route('admin.ai-models.store'), {
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th>{{ trans('Provider') }}</th>
        <th>{{ trans('Name') }}</th>
        <th>{{ trans('Model') }}</th>
        <th>{{ trans('Max Token') }}</th>
        <th>{{ trans('Status') }}</th>
        <th>{{ trans('Created At') }}</th>
        <th class="!text-right">{{ trans('Action') }}</th>
      </tr>
    </thead>
    <tbody v-if="hasModels">
      <tr v-for="aiModel in modelData" :key="aiModel.id">
        <td>{{ aiModel.provider }}</td>
        <td>{{ aiModel.name }}</td>
        <td>{{ aiModel.code }}</td>
        <td>{{ aiModel.max_token }}</td>
        <td>
          <span
            class="inline-block rounded-full px-2 py-1 text-xs font-semibold leading-none"
            :class="
              aiModel.status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            "
          >
            {{ aiModel.status == 'active' ? trans('Active') : trans('Inactive') }}
          </span>
        </td>
        <td>
          {{ new Date(aiModel.created_at).toLocaleDateString() }}
        </td>
        <td class="text-end">
          <div class="dropdown dropdown-end" data-placement="bottom-start">
            <button class="dropdown-toggle btn btn-square btn-ghost" type="button">
              <Icon class="h-5 w-5" icon="bx:dots-horizontal-rounded" />
            </button>
            <div class="dropdown-content w-40">
              <ul class="dropdown-list">
                <li class="dropdown-list-item">
                  <a href="javascript:void(0)" class="dropdown-link" @click="setEditForm(aiModel)">
                    <Icon class="h-5 w-5" icon="fe:edit" />
                    <span>{{ trans('Edit') }}</span>
                  </a>
                </li>
                <li class="dropdown-list-item">
                  <button
                    type="button"
                    class="dropdown-link"
                    @click="deleteRow(route('admin.ai-models.destroy', aiModel))"
                  >
                    <Icon class="h-5 w-5" icon="fe:trash" />
                    <span>{{ trans('Delete') }}</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
    <tbody v-else>
      <tr>
        <td colspan="7" class="py-4 text-center">
          <NoDataFound :forTable="true" />
        </td>
      </tr>
    </tbody>
  </table>
  <Paginate :links="models.links" />

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="isEditing ? trans('Edit Model') : trans('Create Model')"
    :action-btn-state="true"
    :action-btn-text="isEditing ? 'Update' : 'Create'"
    :action-processing="form.processing"
    @action="submit"
    @close="form.reset()"
  >
    <div class="space-y-2">
      <SelectField
        label="Provider"
        v-model="form.provider"
        :options="providers"
        :placeholder="trans('Select Provider')"
        :error="form.errors.provider"
      />
      <InputField v-model="form.name" :label="trans('Name')" :error="form.errors.name" />
      <InputField v-model="form.code" :label="trans('Model')" :error="form.errors.code" />
      <InputField
        v-model="form.max_token"
        :label="trans('Max Token')"
        :error="form.errors.max_token"
      />
      <SelectField
        label="Status"
        v-model="form.status"
        :options="['active', 'inactive']"
        :placeholder="trans('Select Status')"
        :error="form.errors.status"
      />
    </div>
  </Modal>
</template>
