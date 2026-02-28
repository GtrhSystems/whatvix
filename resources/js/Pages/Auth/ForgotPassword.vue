<script setup>
import InputFieldError from '@/Components/InputFieldError.vue'
import { Head, useForm } from '@inertiajs/vue3'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import AuthLayout from '@/Layouts/Auth/AuthLayout.vue'
import LeftBanner from '@/Pages/Auth/Partials/LeftBanner.vue'

defineOptions({ layout: AuthLayout })
const props = defineProps({
  status: {
    type: String
  },
  authPages: 'authPages'
})



const form = useForm({
  email: ''
})

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <Head title="Forgot Password" />
  <div class="signin-banner-area signin-banner-main-wrap d-flex align-items-center">
   <LeftBanner :data="authPages.login ?? {}" />
    <div class="signin-banner-from d-flex justify-content-center align-items-center">
      <div class="signin-banner-from-wrap">
        <div class="signin-banner-title-box">
          <h4 class="signin-banner-from-title">{{ trans('Forget Password') }}</h4>
        </div>
        <p v-if="!status" class="alert alert-primary mb-4">
          {{
            trans(
              'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.'
            )
          }}
        </p>

        <p v-else class="alert alert-success mb-4">
          {{ status }}
        </p>

        <form @submit.prevent="submit">
          <div class="postbox__comment-input mb-3">
            <input
              type="email"
              v-model="form.email"
              autofocus
              autocomplete="email"
              class="inputText"
              :placeholder="trans('enter your email here')"
            />
            <span class="floating-label">{{ trans('Your Email') }}</span>
            <InputFieldError :message="form.errors.email" />
          </div>

          <div class="mt-2" v-if="!status">
            <SpinnerBtn
              class="signin-btn w-100"
              :processing="form.processing"
              btn-text="Email Password Reset Link"
            />
          </div>
        </form>

        <p class="mt-4">
          {{ trans('Have an account?') }}
          <Link :href="route('login')" class="fw-bold">{{ trans('Sign In') }}</Link>
        </p>
      </div>
    </div>
  </div>
</template>
