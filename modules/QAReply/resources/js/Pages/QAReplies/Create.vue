<script setup>
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import InputFieldError from '@/Components/InputFieldError.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

defineOptions({ layout: UserLayout })

const props = defineProps({
  qaReplies: Object
})

const isEditing = computed(() => !!props.qaReplies?.id)

const form = useForm({
  title: props.qaReplies?.title || '',
  items: props.qaReplies?.items || [
    {
      key: '',
      value: ''
    }
  ]
})

const defaultExtraData = {
  key: '',
  value: ''
}

const addItem = () => {
  form.items.push({ ...defaultExtraData })
}

const removeItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

const submit = () => {
  if (isEditing.value) {
    form.patch(`/user/qareply/qareplies/${props.qaReplies.id}`)
  } else {
    form.post('/user/qareply/qareplies')
  }
}
</script>

<template>
  <div class="card card-body mb-2">
    <div>
      <label class="label">{{ trans('Title') }}</label>
      <input type="text" v-model="form.title" class="input" />
      <InputFieldError :message="form.errors.title" />
    </div>
  </div>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="card card-body table shadow-none">
      <thead>
        <tr>
          <th class="w-5/12">{{ trans('Question') }}</th>
          <th class="w-6/12">{{ trans('Answer') }}</th>
          <th class="!text-right">
            <button type="button" @click="addItem" class="btn btn-primary">
              <Icon icon="bx:plus" class="text-lg" />
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(items, index) in form.items" :key="index">
          <td class="text-left">
            <input
              type="text"
              class="input"
              v-model="items.key"
              placeholder="Enter question or command or keyword to search"
            />
            <InputFieldError :message="form.errors[`items.${index}.key`]" />
          </td>
          <td class="text-left">
            <textarea
              class="input"
              v-model="items.value"
              placeholder="Enter answer or response of the query"
            ></textarea>
            <InputFieldError :message="form.errors[`items.${index}.value`]" />
          </td>

          <td>
            <div class="flex justify-end">
              <button type="button" @click="removeItem(index)" class="btn btn-danger">
                <Icon icon="bx:x" class="text-lg" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound :for-table="true" v-if="form.items.length < 1" />
    </table>
    <div class="mt-6 flex justify-end">
      <SpinnerBtn
        type="button"
        @click="submit"
        classes="btn btn-primary"
        :processing="form.processing"
        :btn-text="isEditing ? trans('Update') : trans('Create')"
      />
    </div>
  </div>
</template>
