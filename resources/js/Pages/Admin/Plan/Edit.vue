<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import Multiselect from '@vueform/multiselect'
import { useForm } from '@inertiajs/vue3'
import toast from '@/Composables/toastComposable'
import sharedComposable from '@/Composables/sharedComposable'
defineOptions({ layout: AdminLayout })
const props = defineProps(['segments', 'buttons', 'plan', 'modules'])

const form = useForm({
  ...props.plan,
  plan_data: props.plan.data,
  is_featured: !!props.plan.is_featured,
  is_recommended: !!props.plan.is_recommended,
  is_trial: !!props.plan.is_trial,
  status: !!props.plan.status
})
const defaultExtraData = {
  key: '',
  value: ''
}
const { moduleLabel } = sharedComposable()
const onModuleSelect = (value) => {
  const label = moduleLabel(value)
  if (label === 'WaWeb') {
    form.plan_data = { ...form.plan_data, web_messages: { value: 0, overview: '' } }
  }
  if (label === 'WaCloud') {
    form.plan_data = { ...form.plan_data, cloud_messages: { value: 0, overview: '' } }
  }
}
const onModuleDeselect = (value) => {
  const label = moduleLabel(value)
  if (label === 'WaWeb') {
    delete form.plan_data.web_messages
  }
  if (label === 'WaCloud') {
    delete form.plan_data.cloud_messages
  }
}
const onModuleClear = () => {
  if (form.plan_data.web_messages) {
    delete form.plan_data.web_messages
  }
  if (form.plan_data.cloud_messages) {
    delete form.plan_data.cloud_messages
  }
}
const addItem = () => {
  if (!Array.isArray(form.extra_data)) {
    form.extra_data = []
  }
  form.extra_data.push({ ...defaultExtraData })
}

const removeItem = (index) => {
  form.extra_data.splice(index, 1)
}
function update() {
  form.put(route('admin.plan.update', props.plan.id), {
    onSuccess: () => {
      toast.success('Plan updated successfully')
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <strong>{{ trans('Edit Plan') }}</strong>
      <p>{{ trans('Edit subscription plan for charging from the customer') }}</p>
    </div>

    <form class="flex flex-col-reverse items-start gap-8 lg:flex-row" @submit.prevent="update()">
      <div class="card card-body flex-1">
        <strong>{{ trans('Plan Details') }}</strong>
        <div class="my-2">
          <label>{{ trans('Plan Name') }}</label>
          <input type="text" name="title" required="" class="input" v-model="form.title" />
        </div>
        <div class="my-2">
          <label>{{ trans('Short Description (optional)') }}</label>
          <input
            type="text"
            class="input"
            v-model="form.short_description"
            placeholder="Save ~20% when billed yearly"
          />
        </div>
        <div class="my-2">
          <label>{{ trans('Plan Description') }}</label>
          <textarea v-model="form.description" required class="textarea" />
        </div>
        <div class="my-2">
          <label>{{ trans('Select Duration') }}</label>
          <select class="select" name="days" v-model="form.days">
            <option value="30">{{ trans('Monthly') }}</option>
            <option value="365">{{ trans('yearly') }}</option>
            <option value="999999">{{ trans('Lifetime') }}</option>
          </select>
        </div>
        <div class="my-2">
          <label>{{ trans('Price') }}</label>
          <input type="number" name="price" v-model="form.price" step="any" class="input" />
        </div>

        <div class="mb-2">
          <label for="toggle-featured" class="toggle toggle-sm">
            <input
              v-model="form.is_featured"
              class="toggle-input peer sr-only"
              id="toggle-featured"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Is Featured?') }}</span>
          </label>
        </div>
        <div class="mb-2">
          <label for="toggle-is_recommended" class="toggle toggle-sm">
            <input
              v-model="form.is_recommended"
              class="toggle-input peer sr-only"
              id="toggle-is_recommended"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Is recommended?') }}</span>
          </label>
        </div>
        <div class="mb-2">
          <label for="toggle-is_trial" class="toggle toggle-sm">
            <input
              v-model="form.is_trial"
              class="toggle-input peer sr-only"
              id="toggle-is_trial"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Accept Trial?') }}</span>
          </label>
        </div>
        <template v-if="form.is_trial">
          <div class="from-group trial-days mb-2 mt-2">
            <label class="col-lg-12">{{ trans('Trial days') }}</label>
            <div class="col-lg-12">
              <input
                type="number"
                v-model="form.trial_days"
                name="trial_days"
                class="input"
                required
              />
            </div>
          </div>
        </template>

        <div class="mb-2">
          <label for="toggle-status" class="toggle toggle-sm">
            <input
              v-model="form.status"
              class="toggle-input peer sr-only"
              id="toggle-status"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Activate This Plan?') }}</span>
          </label>
        </div>

        <div class="mt-6">
          <SpinnerBtn classes="btn btn-primary" :processing="form.processing" btn-text="Update" />
        </div>
      </div>

      <div class="card card-body flex-1 space-y-2">
        <strong>{{ trans('Plan Perks') }}</strong>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Modules Access') }}</label>

          <Multiselect
            @select="onModuleSelect"
            @deselect="onModuleDeselect"
            @clear="onModuleClear"
            class="multiselect-dark"
            v-model="form.plan_data.modules.value"
            mode="tags"
            :options="modules"
            placeholder="Select modules access"
            required
          />

          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.devices.overview" required class="input" />
        </div>
        <div class="my-2" v-if="form.plan_data.web_messages">
          <label class="label mb-1">{{ trans('Web Messages (per month)') }}</label>
          <input type="number" v-model="form.plan_data.web_messages.value" required class="input" />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.web_messages.overview" class="input" />
        </div>
        <div class="my-2" v-if="form.plan_data?.cloud_messages">
          <label class="label mb-1">{{ trans('Cloud Messages (per month)') }}</label>
          <input
            type="number"
            v-model="form.plan_data.cloud_messages.value"
            required
            class="input"
          />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.cloud_messages.overview" class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Devices') }}</label>
          <input type="number" v-model="form.plan_data.devices.value" required class="input" />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.devices.overview" required class="input" />
        </div>

        <div class="my-2" v-if="form.plan_data?.chat_flow">
          <label class="label mb-1">{{ trans('Chat Flow') }}</label>
          <input type="number" v-model="form.plan_data.chat_flow.value" required class="input" />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.chat_flow.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Custom Template') }}</label>
          <input
            type="number"
            v-model="form.plan_data.custom_template.value"
            required
            class="input"
          />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.custom_template.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Credits') }}</label>
          <input type="number" v-model="form.plan_data.credits.value" required class="input" />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.credits.overview" required class="input" />
        </div>

        <div class="my-2">
          <label class="label mb-1">{{ trans('Storage') }}</label>
          <input
            type="number"
            v-model="form.plan_data.storage.value"
            step="0.01"
            required
            class="input"
          />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.storage.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Workspaces') }}</label>
          <input type="number" v-model="form.plan_data.workspaces.value" required class="input" />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.workspaces.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Team Members') }}</label>
          <input type="number" v-model="form.plan_data.team_members.value" required class="input" />
          <label class="label mb-1">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.team_members.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('AI Training Dataset') }}</label>
          <input type="number" v-model="form.plan_data.ai_training.value" required class="input" />

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.ai_training.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Web Scraping Query') }}</label>
          <input type="number" v-model="form.plan_data.web_scrape.value" required class="input" />

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.web_scrape.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('My App api') }}</label>
          <input type="number" v-model="form.plan_data.apps.value" required class="input" />

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.apps.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Contacts') }}</label>
          <input type="number" v-model="form.plan_data.contacts.value" required class="input" />

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.contacts.overview" required class="input" />
        </div>
        <div class="my-2">
          <label class="label mb-1">{{ trans('Number Scanning (per month)') }}</label>
          <input
            type="number"
            v-model="form.plan_data.number_scanner.value"
            required
            class="input"
          />

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.number_scanner.overview" required class="input" />
        </div>
        <div class="my-2">
          <label for="toggle-campaign" class="toggle toggle-sm">
            <input
              v-model="form.plan_data.campaign.value"
              class="toggle-input peer sr-only"
              id="toggle-campaign"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Campaign') }}</span>
          </label>

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.campaign.overview" required class="input" />
        </div>
        <div class="my-2">
          <label for="toggle-auto_reply" class="toggle toggle-sm">
            <input
              v-model="form.plan_data.auto_reply.value"
              class="toggle-input peer sr-only"
              id="toggle-auto_reply"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Auto Reply') }}</span>
          </label>

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.auto_reply.overview" required class="input" />
        </div>
        <div class="my-2">
          <label for="toggle-quick_reply" class="toggle toggle-sm">
            <input
              v-model="form.plan_data.quick_reply.value"
              class="toggle-input peer sr-only"
              id="toggle-quick_reply"
              type="checkbox"
            />
            <div class="toggle-body"></div>
            <span class="label label-md">{{ trans('Quick Reply') }}</span>
          </label>

          <label class="label mb-1 block">{{ trans('Overview') }}</label>
          <textarea v-model="form.plan_data.quick_reply.overview" required class="input" />
        </div>
        <div class="mb-2">
          <label class="label mb-2 block font-semibold">{{ trans('Add Extra Perks') }}</label>
          <div
            class="mb-2 flex items-center gap-x-2"
            v-for="(item, index) in form.extra_data"
            :key="index"
          >
            <input type="text" class="input" placeholder="Key" v-model="item.key" />
            <input type="text" class="input" placeholder="Value" v-model="item.value" />

            <button type="button" @click="removeItem(index)" class="btn btn-danger">
              <Icon icon="bx:x" class="text-lg" />
            </button>
          </div>
          <button type="button" @click="addItem" class="btn btn-primary">
            <Icon icon="bx:plus" class="text-lg" />
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
