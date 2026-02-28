<script setup>
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineOptions({ layout: AdminLayout })
const props = defineProps(['id', 'aiProviders'])

const tabs = ['General', 'AI Tools']
const selectedTab = ref(tabs[0])
const activeTabAiProviders = computed(() => {
  if (selectedTab.value === 'General') {
    return {
      OPENAI_API_KEY: props.aiProviders.OPENAI_API_KEY,
      GEMINI_API_KEY: props.aiProviders.GEMINI_API_KEY
    }
  }
  return props.aiProviders
})
const form = useForm({
  ...props.aiProviders
})

function update() {
  form.put(route('admin.developer-settings.update', props.id))
}
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12">
      <div class="lg:col-span-6">
        <strong>{{ trans('Application Ai Api Settings') }}</strong>
        <p>{{ trans('Edit you Ai Api Settings') }}</p>
      </div>
      <div class="lg:col-span-6">
        <form @submit.prevent="update" class="space-y-4">
          <div class="card card-body space-y-4">
            <div>
              <h6>{{ trans('AI API Key Settings') }}</h6>
              <CardDescription>{{
                trans('Edit your AI Api Settings for each provider to enable it')
              }}</CardDescription>
            </div>
            <div class="space-y-4">
              <div class="flex gap-2">
                <button
                  class="btn"
                  type="button"
                  v-for="tab in tabs"
                  :key="tab"
                  :class="selectedTab === tab ? 'btn-info' : 'btn-secondary'"
                  @click="selectedTab = tab"
                >
                  {{ tab }}
                </button>
              </div>
              <div v-for="(provider, key) in activeTabAiProviders" :key="key">
                <label class="mb-2">{{ key.replaceAll('_', ' ') }}</label>
                <input
                  type="text"
                  class="input"
                  v-model="form[key]"
                  placeholder="Enter api key to enable this provider"
                />
              </div>
            </div>
            <div class="mt-2 flex justify-end">
              <SpinnerBtn :processing="form.processing" :btn-text="trans('Save Changes')" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
