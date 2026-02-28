<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import { useForm } from '@inertiajs/vue3'
import { modal } from '@/Composables/modalComposable'
import trans from '@/Composables/transComposable'
import { ref } from 'vue'
defineOptions({ layout: AdminLayout })
const props = defineProps(['version', 'purchaseKey', 'updateData'])

const updating = ref(false)

const form = useForm({
  purchase_key: props.purchaseKey
})

const updateForm = useForm({
  version: props.updateData?.version
})

const submit = () => {
  form.post(route('admin.update.store'), {
    onError: (e) => {
      console.log(e)
    }
  })
}

const update = () => {
  modal.init(null, {
    options: {
      message: trans(
        'Before send a request for update please take a backup of your site with database'
      ),
      confirm_text: trans('Note'),
      accept_btn_text: trans('Proceed for update'),
      reject_btn_text: trans('Cancel')
    },
    callback: () => {
      updating.value = true
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="alert alert-info" role="alert" v-show="updateData?.message">
      <i width="1rem" height="1rem" data-feather="alert-circle"></i>
      <p>
        <b>{{ trans('Note') }}:</b>
        {{ updateData?.message }}
      </p>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex justify-between">
          <h4 class="card-title">{{ trans('Site Update') }}</h4>
          <p>{{ trans('Current Version') }} {{ version }}</p>
        </div>

        <div class="card-body">
          <template v-if="updateData">
            <div class="my-3 flex items-center justify-between rounded-lg border p-4">
              <p>{{ trans('Update Available') }}</p>
              <h6>
                {{ updateData.version }}
              </h6>
              <div>
                <form @submit.prevent="update">
                  <SpinnerBtn :processing="updateForm.processing" :btn-text="trans('Update Now')" />
                </form>
              </div>
            </div>
          </template>

          <!-- show if has not update data -->
          <form @submit.prevent="submit" v-else>
            <div class="mb-2">
              <label for="">{{ trans('Purchase Key') }}</label>
              <input type="text" class="input" :value="purchaseKey" disabled />
            </div>
            <div class="mb-2">
              <SpinnerBtn
                :processing="form.processing"
                :disabled="!form.purchase_key"
                :btn-text="trans('Check New Update')"
              />
            </div>
          </form>
        </div>
      </div>
    </div>

    <div v-if="!updateData" class="alert alert-info" role="alert">
      <i width="1rem" height="1rem" data-feather="alert-circle"></i>
      <p>
        <b>{{ trans('Note') }}:</b>
        {{
          trans(
            'If you have customized the script from codebase do not use this option. you will lose your customization.'
          )
        }}
      </p>
    </div>
  </div>
</template>
