<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import FloatingDropdown from '@/Components/Dashboard/FloatingDropdown.vue'

const props = defineProps({
  action: {
    type: String,
    required: false,
    default: ''
  },
  options: {
    type: [Array, Object],
    required: false,
    default: [
      {
        value: 'id',
        label: 'id',
        selected: true
      }
    ]
  },
  title: {
    type: String,
    required: false,
    default: null
  }
})

const getQueryParams = () => {
  const obj = {}
  const para = new URLSearchParams(window.location.search)

  for (const [key, value] of para) {
    if (obj.hasOwnProperty(key)) {
      if (Array.isArray(obj[key])) {
        obj[key].push(value)
      } else {
        obj[key] = [obj[key], value]
      }
    } else {
      obj[key] = value
    }
  }

  return obj
}

const filterForm = useForm({
  search: getQueryParams()?.search ?? '',
  type:
    getQueryParams()?.type ??
    props.options?.find((item) => item.selected)?.value ??
    props.options?.[0]?.value ??
    ''
})

const handleSubmit = () => {
  filterForm.get(props.action)
}

const clearFilter = () => {
  router.get(location.pathname)
}

const isOptionExist = computed(() => {
  if (props.options && filterForm.type) {
    return props.options.find((item) => item.value == filterForm.type)
  }
  return false
})
</script>

<template>
  <div class="flex items-center justify-between gap-x-2">
    <h5 class="text-slate-500 dark:text-slate-400">
      {{ title ?? $page.props?.pageHeader?.title }}
    </h5>
    <FloatingDropdown
      btn-type="slot"
      btnClass="btn bg-white font-medium shadow-sm dark:bg-slate-800 flex items-center gap-1"
    >
      <template #btnContent>
        <Icon class="text-xl" icon="material-symbols:filter-alt-outline-sharp" />
        <span>{{ trans('Search') }}</span>
        <Icon class="text-xl" icon="tdesign:chevron-down" />
      </template>
      <div class="w-72 !overflow-visible">
        <form @submit.prevent="handleSubmit">
          <ul class="dropdown-list space-y-2 p-4">
            <!-- show if select has not any options -->
            <li v-if="isOptionExist && isOptionExist.options" class="flex flex-col">
              <label class="label mb-1 capitalize">{{ filterForm.type }}</label>

              <select class="select" v-model="filterForm.search">
                <option value="" disabled selected>
                  {{ trans('Select') }}
                </option>
                <option
                  v-for="option in isOptionExist.options"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
            </li>

            <li v-else class="flex flex-col">
              <label class="label mb-1">{{ trans('Search Keyword') }}</label>
              <input
                type="text"
                v-model="filterForm.search"
                class="input"
                placeholder="Search keyword"
              />
            </li>

            <li class="flex flex-col">
              <label class="label mb-1">{{ trans('Search Type') }}</label>
              <select class="select" @change="filterForm.reset('search')" v-model="filterForm.type">
                <option
                  v-for="option in options"
                  :key="option.value"
                  :value="option.value"
                  :selected="option.selected"
                >
                  {{ option.label }}
                </option>
              </select>
            </li>

            <li class="flex items-start gap-x-2">
              <SpinnerBtn
                :disabled="!filterForm.search"
                type="submit"
                :processing="filterForm.processing"
                :btn-text="trans('Search')"
                class="btn btn-primary w-full"
                icon="fe:search"
              />
              <button
                @click="clearFilter"
                class="btn btn-soft-danger transition-all"
                :disabled="!getQueryParams().search"
                title="clear filter"
                type="button"
              >
                <Icon icon="bx:x" class="size-5" />
              </button>
            </li>
          </ul>
        </form>
      </div>
    </FloatingDropdown>
  </div>
</template>
