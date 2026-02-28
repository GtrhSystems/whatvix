<script setup>
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import InputFieldError from '@/Components/InputFieldError.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: UserLayout })
const { textExcerpt, deleteRow, badgeClass } = sharedComposable()

const props = defineProps(['providers'])

const form = useForm({
  title: '',
  provider: '',
  dataset: []
})

const defaultExtraData = {
  question: '',
  answer: ''
}

const addItem = () => {
  form.dataset.push({ ...defaultExtraData })
}

const removeItem = (index) => {
  form.dataset.splice(index, 1)
}

const submit = () => {
  form.post(route('user.ai-training.store'))
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-end justify-between">
      <div>
        <strong>{{ trans('Create Ai Training Dataset') }}</strong>
        <p class="text-sm">
          {{ trans('Create a new dataset for training the AI model.') }}
        </p>
      </div>
      <button type="button" @click="addItem" class="btn btn-primary">
        <Icon icon="bx:plus" class="text-lg" />
        <span>{{ trans('Add Dataset') }}</span>
      </button>
    </div>
    <div class="card card-body grid grid-cols-2 gap-6">
      <div class="">
        <label class="label">{{ trans('Title') }}</label>
        <input type="text" placeholder="Title" v-model="form.title" class="input" />
        <InputFieldError :message="form.errors.title" />
      </div>
      <div class="">
        <label class="label">{{ trans('Provider') }}</label>
        <select name="provider" class="select capitalize" v-model="form.provider">
          <option value="">{{ trans('Select Provider') }}</option>
          <option v-for="provider in providers" :key="provider" :value="provider">
            {{ provider }}
          </option>
        </select>

        <InputFieldError :message="form.errors.provider" />
      </div>
    </div>
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th class="w-5/12">{{ trans('Command Or Question') }}</th>
            <th class="w-6/12">{{ trans('Answer') }}</th>
            <th class="!text-right">{{ trans('Action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(dataset, index) in form.dataset" :key="index">
            <td class="text-left">
              <input type="text" class="input" v-model="dataset.question" />
              <InputFieldError :message="form.errors[`dataset.${index}.question`]" />
            </td>
            <td class="text-left">
              <textarea name="answer" class="input" v-model="dataset.answer"></textarea>
              <InputFieldError :message="form.errors[`dataset.${index}.answer`]" />
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
        <NoDataFound :for-table="true" v-if="form.dataset.length < 1" />
      </table>
    </div>
    <div class="mt-6 flex justify-end">
      <SpinnerBtn
        type="button"
        @click="submit"
        classes="btn btn-primary"
        :processing="form.processing"
        btn-text="Create"
      />
    </div>
  </div>
</template>
