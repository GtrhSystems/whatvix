<script setup>
import { ref, onMounted, computed } from 'vue'
import { modal } from '@/Composables/actionModalComposable'
import trans from '@/Composables/transComposable'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import NoDataFound from '@/Components/NoDataFound.vue'
import HollowDotsSpinner from '@/Components/HollowDotsSpinner.vue'
import sharedComposable from '@/Composables/sharedComposable'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import toastComposable from '@/Composables/toastComposable'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'

defineOptions({ layout: UserLayout })

const { deleteRow } = sharedComposable()
const props = defineProps(['record', 'scraped_data'])
const places = ref([])
const nextPageToken = ref(null)
const isLoading = ref(false)
const hasSavedData = ref(false)

const fetchPlaces = async () => {
  isLoading.value = true
  try {
    const response = await axios.post(
      route('user.web-scraping.api.scrape.index', props.record.uuid)
    )

    places.value = [...places.value, ...response.data.data]
    nextPageToken.value = response.data.next_page_token
  } catch (error) {
    toastComposable.danger(error.response?.data ?? 'Error fetching places')
  }
  isLoading.value = false
}

const submit = () => {
  isLoading.value = true
  router.patch(
    route('user.web-scraping.scrape.store_data', props.record.uuid),
    {},
    {
      onSuccess: () => {
        isLoading.value = false
        toastComposable.success('Places added successfully')
      },
      onFinish: () => {
        isLoading.value = false
      }
    }
  )
}

onMounted(() => {
  if (props.scraped_data?.data?.length > 0) {
    places.value = props.scraped_data.data.map((item) => {
      return {
        id: item.id,
        ...item.data
      }
    })
    hasSavedData.value = true
  } else {
    fetchPlaces()
  }
})

const loadMore = async () => {
  if (!nextPageToken.value) {
    return toastComposable.danger('No more places to load')
  }
  isLoading.value = true
  try {
    const response = await axios.post(
      route('user.web-scraping.api.scrape.index', props.record.uuid),
      { next_page_token: nextPageToken.value }
    )
    places.value = [...places.value, ...response.data.data]
    nextPageToken.value = response.data.next_page_token
  } catch (error) {
    toastComposable.danger(error.response?.data ?? 'Error fetching places')
  }
  isLoading.value = false
}

const placesData = computed(() => places.value)

const deleteData = (id) => {
  modal.init(route('user.web-scraping.scrape.destroy_data', id), {
    method: 'delete',
    options: {
      message: trans('You would not be revert it back!'),
      confirm_text: trans('Are you sure?'),
      accept_btn_text: trans('Yes, Sure!'),
      reject_btn_text: trans('No, Cancel')
    },
    callback: () => {
      places.value = places.value.filter((place) => place.id !== id)
    }
  })
}
</script>

<template>
  <div class="flex justify-between">
    <div class="flex items-center gap-4">
      <a
        v-if="hasSavedData"
        class="btn btn-primary"
        :href="route('user.web-scraping.scrape.export_data', record.id)"
        target="_blank"
      >
        <Icon icon="bx:export" />
        <span>{{ trans('Export Data') }}</span>
      </a>
      <SpinnerBtn
        v-if="record.status !== 'completed' && hasSavedData"
        type="button"
        icon="bx:refresh"
        @click="submit"
        :processing="isLoading"
        btnText="Sync Number"
      />
    </div>
  </div>
  <div
    class="table-responsive mt-6 w-full"
    v-if="(hasSavedData && !isLoading) || placesData.length"
  >
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
            <span v-if="!hasSavedData">{{ trans('Hidden') }}</span>
            <span v-else>{{ place.phone_number || 'N/A' }}</span>
          </td>
          <td>{{ place.formatted_address }}</td>
          <td>{{ place.rating || 'N/A' }}</td>
          <td class="capitalize">{{ place.types.join(', ') }}</td>

          <td class="!text-right">
            <button class="btn btn-danger btn-sm" @click="deleteData(place.id)">
              <Icon icon="bx:trash" />
            </button>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
  </div>
  <NoDataFound v-else-if="!isLoading && !hasSavedData && !placesData.length" />
  <div class="flex items-center justify-center" v-if="isLoading">
    <HollowDotsSpinner v-if="true" class="my-3 scale-150" />
  </div>
  <Paginate :links="scraped_data.links" />
  <div v-if="!hasSavedData" class="mt-4 flex justify-end gap-4">
    <SpinnerBtn
      icon="bx:refresh"
      type="button"
      @click="loadMore"
      :processing="isLoading"
      :disabled="!nextPageToken || isLoading"
      btnText="Load More"
    />
  </div>
</template>
