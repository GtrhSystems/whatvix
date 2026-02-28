<script setup>
import { defineAsyncComponent, computed, ref } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()
const props = defineProps({
  title: String,
  buttons: Array,
  overviews: Array,
  segments: Array,
  classes: String
})
const isProgress = ref(false)
router.on('start', () => (isProgress.value = true))
router.on('finish', () => (isProgress.value = false))
const title = computed(() => props.title ?? usePage().props.pageHeader.title ?? '')
const buttons = computed(() => props.buttons ?? usePage().props.pageHeader.buttons ?? [])
const overviews = computed(() => props.overviews ?? usePage().props.pageHeader.overviews ?? [])
const segments = computed(() => props.segments ?? usePage().props.pageHeader.segments ?? [])
const classes = computed(() => props.classes ?? usePage().props.pageHeader.classes ?? '')

const OverviewGrid = defineAsyncComponent(() => import('@/Components/Dashboard/OverviewGrid.vue'))
</script>

<template>
  <Head :title="title" />

  <div
    :class="classes"
    class="mb-4 flex flex-col justify-between gap-x-4 gap-y-2 md:flex-row md:gap-y-0"
  >
    <ol class="breadcrumb min-w-max">
      <template v-if="segments.length">
        <li v-for="segment in segments" :key="segment.index" class="breadcrumb-item capitalize">
          {{ segment }}
        </li>
      </template>
    </ol>
    <template v-if="buttons.length">
      <div class="flex flex-wrap justify-end gap-3">
        <template v-for="button in buttons" :key="button.index">
          <Link
            v-if="button.type === 'link'"
            :href="button.url"
            class="btn btn-sm btn-primary max-w-max flex-1 whitespace-nowrap"
          >
            <Icon
              v-if="button.animate && isProgress"
              icon="bx:refresh"
              class="mt-0.5 size-4 animate-spin"
            />
            <Icon v-else-if="button.icon" :icon="button.icon" class="mt-0.5 size-4" />
            <span>{{ button.text }}</span>
          </Link>

          <button
            v-else-if="button.type == 'modal'"
            @click="modalStore.open(button.target)"
            class="btn btn-sm btn-primary max-w-max flex-1 whitespace-nowrap"
            type="button"
          >
            <Icon v-if="button.icon" class="mt-px h-4 w-4" :icon="button.icon" />
            <span>{{ button.text }}</span>
          </button>

          <a
            v-else
            class="btn btn-sm btn-primary max-w-max flex-1 whitespace-nowrap"
            :target="button.target ?? '_self'"
            :href="button.url"
          >
            <Icon v-if="button.icon" :icon="button.icon" />
            <span>{{ button.text }}</span>
          </a>
        </template>
      </div>
    </template>
  </div>

  <OverviewGrid :items="overviews" :grid="overviews.length > 4 ? 4 : overviews.length" />
</template>
