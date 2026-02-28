<script setup>
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import { computed, onMounted, ref } from 'vue'

defineOptions({ layout: UserLayout })

const { deleteRow } = sharedComposable()
const props = defineProps(['record', 'scraped_data'])
const places = ref([])
onMounted(() => {
  places.value = props.scraped_data.data.map((item) => {
    return {
      id: item.id,
      ...item.data
    }
  })
})
const placesData = computed(() => places.value)
</script>

<template>
  <div class="table-responsive mt-6 w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Name') }}</th>
          <th class="whitespace-nowrap">{{ trans('Phone Number') }}</th>
          <th>{{ trans('Address') }}</th>
          <th>{{ trans('Rating') }}</th>
          <th>{{ trans('Types') }}</th>
          <th class="!text-right">{{ trans('Action') }}</th>
        </tr>
      </thead>
      <tbody v-if="placesData.length" class="tbody">
        <tr v-for="(place, index) in placesData" :key="index">
          <td>{{ place.name }}</td>
          <td>
            <span>{{ place.phone_number || 'N/A' }}</span>
          </td>
          <td>{{ place.formatted_address }}</td>
          <td>{{ place.rating || 'N/A' }}</td>
          <td class="capitalize">{{ place.types.join(', ') }}</td>

          <td class="!text-right">
            <button
              class="btn btn-danger btn-sm"
              @click="deleteRow(route('admin.web-scraping.destroy_data', place.id))"
            >
              <Icon icon="bx:trash" />
            </button>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
  </div>

  <Paginate :links="scraped_data.links" />
</template>
