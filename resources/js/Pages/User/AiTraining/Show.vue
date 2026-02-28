<script setup>
import Pagination from '@/Components/Dashboard/Paginate.vue'
import FloatingDropdown from '@/Components/Dashboard/FloatingDropdown.vue'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import { router } from '@inertiajs/vue3'

defineOptions({ layout: UserLayout })
const props = defineProps(['aiTrainings', 'provider', 'isDev'])
const { textExcerpt, deleteRow, badgeClass, trim } = sharedComposable()

const checkStatus = (aiTraining) => {
  router.patch(route('user.ai-training.check-status', aiTraining.id))
}
</script>
<template>
  <div class="table-responsive whitespace-nowrap rounded-primary">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Title') }}</th>
          <th>{{ trans('File Name In Provider') }}</th>

          <th class="text-right">{{ trans('Status') }}</th>
          <th class="flex justify-end">{{ trans('Action') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="aiTraining in aiTrainings.data" :key="aiTraining.id">
          <td>
            {{ textExcerpt(aiTraining.title, 50) }}
          </td>
          <td class="text-left">{{ aiTraining?.model_name ?? 'N/A' }}</td>

          <td class="text-right capitalize">
            <span :class="badgeClass(aiTraining.status)">
              {{ trim(aiTraining.status) }}
            </span>
          </td>

          <td class="flex justify-end">
            <FloatingDropdown
              position="right"
              btn-type="icon"
              icon-name="bx:dots-vertical-rounded"
              btn-class="p-0"
            >
              <ul class="dropdown-list">
                <li class="dropdown-list-item" v-if="aiTraining.status !== 'success'">
                  <button type="button" class="dropdown-link" @click="checkStatus(aiTraining)">
                    <Icon icon="bx:log-in-circle" />
                    <span>{{ trans('Check Status') }}</span>
                  </button>
                </li>
                <li class="dropdown-list-item" v-if="isDev">
                  <Link
                    :href="route('user.ai-training.test-prompt', aiTraining.id)"
                    class="dropdown-link"
                  >
                    <Icon icon="material-symbols:network-check-rounded" />
                    <span>{{ trans('Test Prompt') }}</span>
                  </Link>
                </li>
                <li class="dropdown-list-item">
                  <button
                    type="button"
                    class="dropdown-link"
                    @click="deleteRow(route('user.ai-training.destroy-record', aiTraining.id))"
                  >
                    <Icon icon="bx:trash-alt" />
                    <span>{{ trans('Delete From DB') }}</span>
                  </button>
                </li>
                <li class="dropdown-list-item">
                  <button
                    type="button"
                    class="dropdown-link"
                    @click="deleteRow(route('user.ai-training.destroy', aiTraining.id))"
                  >
                    <Icon icon="bx:trash" />
                    <span>{{ trans('Delete With Model') }}</span>
                  </button>
                </li>
              </ul>
            </FloatingDropdown>
          </td>
        </tr>
      </tbody>
      <NoDataFound :for-table="true" v-if="aiTrainings.data.length < 1" />
    </table>
  </div>

  <Pagination :links="aiTrainings.links" />
</template>
