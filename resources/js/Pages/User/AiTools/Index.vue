<script setup>
import { router } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import toast from '@/Composables/toastComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
defineOptions({ layout: UserLayout })
const props = defineProps(['templates', 'credits', 'bookmarked', 'segments'])
const { textExcerpt, uiAvatar, trim } = sharedComposable()

const bookmark = (id, bookmarked) => {
  router.post(
    route('user.ai-tools.bookmark'),
    { ai_template_id: id },
    {
      onSuccess: () => {
        if (bookmarked == 1) {
          toast.danger('Bookmarked Removed Successfully')
        } else {
          toast.success('Bookmarked Successfully')
        }
      }
    }
  )
}
const filterOptions = [
  {
    label: 'Title',
    value: 'title'
  },
  {
    label: 'Description',
    value: 'description'
  }
]
</script>

<template>
  <FilterDropdown :options="filterOptions" />
  <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
    <template v-for="temp in templates" :key="temp.id">
      <div class="card card-body">
        <div class="flex items-center justify-start gap-4 border-b border-primary-600 pb-8">
          <Link :href="route('user.ai-tools.show', temp.uuid)" class="logo h-16 min-w-[4rem]">
            <img
              v-lazy="uiAvatar(temp.title, temp.icon)"
              alt="image"
              class="h-full w-full rounded-full"
            />
          </Link>
          <div>
            <Link :href="route('user.ai-tools.show', temp.uuid)" class="font-semibold capitalize">
              {{ textExcerpt(temp.title, 65) }}
            </Link>
            <p class="text-sm capitalize text-gray-500">{{ trim(temp.prompt_type) }}</p>
          </div>

          <button
            @click="bookmark(temp.id, temp.isBookmarked)"
            type="button"
            class="btn ml-auto h-10 w-10 rounded-full"
            :class="{
              'btn-primary': temp.isBookmarked == 1,
              'btn-outline-primary': temp.isBookmarked == 0
            }"
          >
            <i class="bx bx-bookmark-alt-minus text-xl"></i>
          </button>
        </div>
        <div v-html="textExcerpt(temp.description, 100)" class="pt-5"></div>
      </div>
    </template>
  </div>

  <NoDataFound v-if="templates.length < 1" />
</template>
