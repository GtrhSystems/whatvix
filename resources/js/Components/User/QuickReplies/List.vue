<script setup>
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import sharedComposable from '@/Composables/sharedComposable'

const props = defineProps({
  module: {
    type: String,
    required: true
  },
  quickReplies: {
    type: Object,
    required: true
  }
})
const { deleteRow, textExcerpt, badgeClass } = sharedComposable()
</script>

<template>
  <div class="table-responsive mt-4 w-full">
    <table class="table">
      <thead>
        <tr>
          <th class="w-8">{{ trans('#') }}</th>
          <th>{{ trans('Message Template') }}</th>
          <th class="w-32">{{ trans('Status') }}</th>
          <th class="w-32 !text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="quickReplies.data.length" class="tbody">
        <tr v-for="(template, index) in quickReplies.data" :key="index">
          <td>
            {{ index + 1 }}
          </td>
          <td>
            {{ textExcerpt(template.message_template, 160) }}
          </td>
          <td>
            <span :class="badgeClass(template.status)">{{
              template.status === 'active' ? 'active' : 'inactive'
            }}</span>
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <component
                        :is="$page.props?.activeModule === module ? 'Link' : 'a'"
                        class="dropdown-link"
                        :href="route(`user.${module}.quick-replies.edit`, template.id)"
                      >
                        <Icon icon="bx:bxs-edit-alt" />
                        <span>{{ trans('Edit') }}</span>
                      </component>
                    </li>

                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        @click="
                          deleteRow(route(`user.${module}.quick-replies.destroy`, template.id))
                        "
                      >
                        <Icon icon="bx:trash" />
                        {{ trans('Delete') }}
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
    <div class="w-full">
      <Paginate v-if="quickReplies.data.length" :links="quickReplies.links" />
    </div>
  </div>
</template>
