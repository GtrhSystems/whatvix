<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputFieldError from '@/Components/InputFieldError.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['user'])
const avatarPreview = computed(() => {
  if (form.avatar) {
    return URL.createObjectURL(form.avatar)
  }
  return props.user.avatar
    ? props.user.avatar
    : 'https://ui-avatars.com/api/?name=' + props.user.name
})

const form = useForm({
  avatar: null,
  name: props.user.name,
  email: props.user.email,
  current_password: '',
  _method: 'put'
})

const submit = () => {
  form.post(route('user.account-settings.update'), {
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="card mx-auto max-w-3xl p-5">
      <h4 class="mb-4">{{ trans('Edit Profile') }}</h4>
      <form @submit.prevent="submit" class="space-y-1">
        <div class="flex items-center gap-3">
          <img :src="avatarPreview" class="h-20 w-20 rounded-full border border-gray-300" />
          <div>
            <label class="label mb-1">{{ trans('Avatar') }}</label>
            <input class="input" type="file" @input="form.avatar = $event.target.files[0]" />
            <InputFieldError :message="form.errors.avatar" />
          </div>
        </div>

        <div>
          <label class="label mb-1">{{ trans('Name') }}</label>
          <input class="input" type="text" v-model="form.name" placeholder="Zubayer" />
          <InputFieldError :message="form.errors.name" />
        </div>
        <template v-if="!props.user.provider && !props.user.provider_id">
          <div>
            <label class="label mb-1">{{ trans('Email') }}</label>
            <input
              class="input"
              type="email"
              v-model="form.email"
              placeholder="zubayerhasan@gmal.com"
            />
            <InputFieldError :message="form.errors.email" />
          </div>
          <div>
            <label class="label mb-1">{{ trans('Current Password') }}</label>
            <input
              class="input"
              type="password"
              v-model="form.current_password"
              placeholder="enter your current password"
            />

            <InputFieldError :message="form.errors.current_password" />

            <div class="mt-3 text-sm">
              {{ trans('Want to change the password?') }}
              <Link :href="route('user.change-password')" class="font-medium text-primary-800">
                {{ trans('Click here') }}</Link
              >
            </div>
          </div>
        </template>
        <SpinnerBtn
          type="submit"
          :processing="form.processing"
          class="btn btn-primary mb-4 mt-6"
          :btn-text="trans('Update Information')"
        />
      </form>
    </div>
  </div>
</template>
