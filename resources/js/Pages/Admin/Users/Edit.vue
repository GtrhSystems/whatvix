<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import trans from '@/Composables/transComposable'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import { useForm } from '@inertiajs/vue3'
import ImageInput from '@/Components/Forms/ImageInput.vue'
const { deleteRow, formatCurrency } = sharedComposable()
defineOptions({ layout: AdminLayout })

const props = defineProps(['userInfo'])

const editForm = useForm({
  name: props.userInfo.name,
  avatar: '',
  credits: props.userInfo.credits,
  email: props.userInfo.email,
  password: '',
  status: props.userInfo.status ? true : false,
  email_verified_at: props.userInfo.email_verified_at != null ? true : false,

  meta: props.userInfo.meta,
  _method: 'PATCH'
})

const updateUser = () => {
  editForm.post(route('admin.users.update', props.userInfo.id), {
    preserveState: false
  })
}
</script>

<template>
  <form @submit.prevent="updateUser()">
    <div class="mx-auto flex w-[650px] flex-col gap-5">
      <div class="card">
        <h6 class="card-header -mb-2">{{ trans('Basic Information') }}</h6>
        <div class="card-body">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label mb-1">{{ trans('Name') }}</label>
              <input type="text" class="input" v-model="editForm.name" />
            </div>

            <ImageInput v-model="editForm.avatar" label="Avatar" />

            <div>
              <label class="label mb-1">{{ trans('Email') }}</label>
              <input type="email" class="input" v-model="editForm.email" />
            </div>
            <div>
              <label class="label mb-1"> {{ trans('Credits') }}</label>
              <input type="number" class="input" v-model="editForm.credits" />
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <h6 class="card-header -mb-2">{{ trans('Others') }}</h6>
        <div class="card-body">
          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <div>
                <label for="toggle-checked-input0" class="toggle label">
                  <input
                    class="toggle-input peer sr-only"
                    id="toggle-checked-input0"
                    type="checkbox"
                    v-model="editForm.status"
                  />
                  <div class="toggle-body"></div>
                  <span class="label">{{ trans('Is Active?') }}</span>
                </label>
              </div>
              <div>
                <label for="toggle-checked-input1" class="toggle label">
                  <input
                    class="toggle-input peer sr-only"
                    id="toggle-checked-input1"
                    type="checkbox"
                    v-model="editForm.email_verified_at"
                  />
                  <div class="toggle-body"></div>
                  <span class="label">{{ trans('Email Verified') }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="label mb-1"> {{ trans('Password') }}</label>
              <input type="password" class="input" v-model="editForm.password" />
            </div>
          </div>

          <div class="mt-5 text-end">
            <SpinnerBtn
              classes="btn btn-primary"
              :processing="editForm.processing"
              :btn-text="trans('Update user')"
            />
          </div>
        </div>
      </div>
    </div>
  </form>
</template>
