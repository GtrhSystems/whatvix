<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import trans from '@/Composables/transComposable'
defineOptions({ layout: AdminLayout })
const { textExcerpt, deleteRow } = sharedComposable()
const props = defineProps([
  'pages',
  'totalActivePosts',
  'totalInActivePosts',
  'totalPosts',
  'buttons'
])
const pageStats = [
  {
    value: props.totalPosts,
    title: trans('Total Page'),
    iconClass: 'bx bx-bar-chart'
  },
  {
    value: props.totalActivePosts,
    title: trans('Active Page'),
    iconClass: 'bx bx-check-circle'
  },
  {
    value: props.totalInActivePosts,
    title: trans('Inactive Page'),
    iconClass: 'bx bx-x-circle'
  }
]
</script>

<template>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Title') }}</th>
          <th>{{ trans('Url') }}</th>
          <th>{{ trans('Status') }}</th>
          <th class="!text-right">{{ trans('Action') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="page in pages.data" :key="page.id">
          <td class="text-left">
            {{ textExcerpt(page.title, 50) }}
          </td>
          <td class="text-left">
            <a :href="`/${page.slug}`" target="_blank"> /{{ textExcerpt(page.slug, 100) }} </a>
          </td>

          <td class="text-left">
            <span class="badge" :class="page.status == 1 ? 'badge-success' : 'badge-danger'">
              {{ page.status == 1 ? trans('Active') : trans('Draft') }}
            </span>
          </td>
          <td>
            <div class="dropdown" data-placement="bottom-start">
              <div class="dropdown-toggle">
                <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
              </div>
              <div class="dropdown-content w-40">
                <ul class="dropdown-list">
                  <li class="dropdown-list-item">
                    <Link :href="route('admin.page.edit', page.id)" class="dropdown-link">
                      <Icon class="h-6 text-slate-400" icon="material-symbols:edit-outline" />
                      <span>{{ trans('Edit') }}</span>
                    </Link>
                  </li>

                  <li class="dropdown-list-item">
                    <button
                      as="button"
                      class="dropdown-link"
                      @click="deleteRow(route('admin.page.destroy', page.id))"
                    >
                      <Icon class="h-6" icon="material-symbols:delete-outline" />
                      <span>{{ trans('Delete') }}</span>
                    </button>
                  </li>
                </ul>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <Paginate :links="pages.links" />
  </div>
</template>
