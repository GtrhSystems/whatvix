<script setup>
import { computed, onMounted } from 'vue'

import { useForm, Head } from '@inertiajs/vue3'
import MultiSelect from '@/Components/Forms/MultiSelect.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import BlankLayout from '@/Layouts/BlankLayout.vue'
import InputFieldError from '@/Components/InputFieldError.vue'

defineOptions({ layout: BlankLayout })

const props = defineProps(['modules', 'workspace'])

const isEditing = computed(() => !!props.workspace?.id)

const form = useForm({
  name: props.workspace?.name,
  description: props.workspace?.description,
  modules: props.workspace?.modules ?? []
})

console.log(props.modules)

const submit = () => {
  isEditing.value ? update() : store()
}

const store = () => {
  form.post(route('user.workspaces.store'))
}

const update = () => {
  form.put(route('user.workspaces.update', props.workspace.id))
}
</script>

<template>
  <Head :title="trans(isEditing ? 'Update Workspace' : 'Create New Workspace')" />
  <div class="container flex h-screen items-center justify-center px-3">
    <div class="card card-body mx-auto max-w-2xl">
      <form @submit.prevent="submit">
        <p class="font-semibold md:text-xl">
          {{ trans(isEditing ? 'Update Workspace' : 'Create New Workspace') }}
        </p>
        <div class="mt-3 flex flex-col gap-1">
          <div>
            <label for="name" class="label mb-1"> {{ trans('Workspace Name') }}</label>
            <input type="text" v-model="form.name" class="input" placeholder="Workspace Name" />
            <InputFieldError :message="form.errors.name" />
          </div>
          <div>
            <label for="description" class="label mb-1">
              {{ trans('Workspace Description') }}</label
            >
            <textarea
              v-model="form.description"
              class="textarea"
              placeholder="Workspace Description"
            ></textarea>
            <InputFieldError :message="form.errors.description" />
          </div>

          <div>
            <label for="modules" class="label mb-1"> {{ trans('Modules') }}</label>
            <MultiSelect v-model="form.modules" :options="modules" />
            <InputFieldError :message="form.errors.modules" />
          </div>

          <div class="mt-2 flex flex-wrap justify-between gap-2 sm:whitespace-nowrap">
            <Link
              :href="route('user.workspaces.index')"
              class="btn btn-secondary flex-1 md:flex-initial"
            >
              {{ trans('Back to Workspaces') }}
            </Link>
            <SpinnerBtn
              class="flex-1 md:flex-initial"
              :processing="form.processing"
              :btn-text="trans(isEditing ? 'Update Workspace' : 'Create Workspace')"
            />
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
