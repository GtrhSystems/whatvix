<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { useForm } from '@inertiajs/vue3'

import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import TextInput from '@/Components/Dashboard/TextInput.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps(['buttons', 'segments'])
const form = useForm({
  member_name: '',
  member_position: '',
  profile_picture: '',
  about: '',
  status: false,
  information: {
    location: '',
    email: '',
    age: '',
    qualification: '',
    gender: 'male'
  },
  socials: {
    facebook: null,
    twitter: null,
    linkedin: null,
    instagram: null
  }
})
const createTeam = () => {
  form.post(route('admin.team.store'))
}
</script>

<template>
  <div class="space-y-6">
    <form method="post" @submit.prevent="createTeam" enctype="multipart/form-data">
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
        <div class="lg:col-span-4">
          <strong>{{ trans('Create a team member') }}</strong>
          <p>
            {{ trans('Add your team member details and necessary information from here') }}
          </p>
        </div>
        <div class="card-wrapper lg:col-span-8">
          <div class="card">
            <div class="card-body">
              <div class="mb-2">
                <label>{{ trans('Member Name') }}</label>
                <input
                  v-model="form.member_name"
                  type="text"
                  name="member_name"
                  required
                  class="input"
                />
              </div>
              <div class="mb-2">
                <label>{{ trans('Position') }}</label>
                <input
                  v-model="form.member_position"
                  type="text"
                  name="member_position"
                  required
                  class="input"
                />
              </div>
              <div class="mb-2">
                <label>{{ trans('Profile Picture') }}</label>
                <input
                  @input="(e) => (form.profile_picture = e.target.files[0])"
                  type="file"
                  accept="image/*"
                  name="profile_picture"
                  required
                  class="input"
                />
              </div>

              <div class="mb-2">
                <label>{{ trans('Profile Description') }}</label>
                <textarea
                  v-model="form.about"
                  class="input h-200"
                  name="about"
                  maxlength="1000"
                  required
                ></textarea>
              </div>

              <TextInput :label="trans('Location')" v-model="form.information.location" />
              <TextInput :label="trans('Email')" v-model="form.information.email" />
              <TextInput :label="trans('Age')" v-model="form.information.age" />
              <TextInput :label="trans('Qualification')" v-model="form.information.qualification" />

              <div class="mb-2">
                <label>{{ trans('Gender') }}</label>
                <select v-model="form.information.gender" class="select">
                  <option value="male">{{ trans('Male') }}</option>
                  <option value="female">{{ trans('Female') }}</option>
                  <option value="other">{{ trans('Other') }}</option>
                </select>
              </div>

              <div class="mb-2">
                <label>{{ trans('Facebook profile link') }}</label>
                <input
                  v-model="form.socials.facebook"
                  type="url"
                  name="socials[facebook]"
                  class="input"
                />
              </div>
              <div class="mb-2">
                <label>{{ trans('Twitter profile link') }}</label>
                <input
                  v-model="form.socials.twitter"
                  type="url"
                  name="socials[twitter]"
                  class="input"
                />
              </div>
              <div class="mb-2">
                <label>{{ trans('Linkedin profile link') }}</label>
                <input
                  v-model="form.socials.linkedin"
                  type="url"
                  name="socials[linkedin]"
                  class="input"
                />
              </div>
              <div class="mb-2">
                <label>{{ trans('Instagram profile link') }}</label>
                <input
                  v-model="form.socials.instagram"
                  type="url"
                  name="socials[instagram]"
                  class="input"
                />
              </div>
              <div class="mb-2">
                <label for="toggle-status" class="toggle toggle-sm">
                  <input
                    v-model="form.status"
                    class="toggle-input peer sr-only"
                    id="toggle-status"
                    type="checkbox"
                  />
                  <div class="toggle-body"></div>
                  <span class="label label-md">{{ trans('Make it publish?') }}</span>
                </label>
              </div>

              <div class="from-group row mt-3">
                <div class="col-lg-12">
                  <SpinnerBtn :processing="form.processing" :btn-text="trans('Save Changes')" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
