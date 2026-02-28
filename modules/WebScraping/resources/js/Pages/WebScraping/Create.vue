<script setup>
import { useForm } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import InputField from '@/Components/Forms/InputField.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
defineOptions({ layout: UserLayout })
const props = defineProps(['categories'])
const form = useForm({
  title: '',
  type: 'google_places',
  category_id: '',
  parameters: {
    country: '',
    city: '',
    state: ''
  }
})

const submit = () => {
  form.post(route('user.web-scraping.scrape.store'))
}
</script>

<template>
  <div class="container mx-auto p-4">
    <PageHeader />
    <div class="card card-body mx-auto max-w-3xl">
      <h1 class="mb-4 text-xl font-semibold">{{ trans('Create Web Scraping Task') }}</h1>
      <form @submit.prevent="submit" class="space-y-2">
        <InputField
          label="Title"
          v-model="form.title"
          placeholder="Title"
          :validationMessage="form.errors.title"
        />

        <div>
          <label class="label mb-1">{{ trans('Category') }}</label>
          <select v-model="form.category_id" class="select">
            <option value="">{{ trans('Select Category') }}</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.title }}
            </option>
          </select>
        </div>

        <InputField
          label="Country"
          v-model="form.parameters.country"
          placeholder="Country"
          :validationMessage="form.errors['parameters.country']"
        />

        <InputField
          label="State"
          v-model="form.parameters.state"
          placeholder="State"
          :validationMessage="form.errors['parameters.state']"
        />
        <InputField
          label="City"
          v-model="form.parameters.city"
          placeholder="City"
          :validationMessage="form.errors['parameters.city']"
        />

        <SpinnerBtn :processing="form.processing" btn-text="Create" />
      </form>
    </div>
  </div>
</template>
