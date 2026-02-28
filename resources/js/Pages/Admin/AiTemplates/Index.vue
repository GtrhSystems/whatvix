<script setup>
import moment from 'moment'
import trans from '@/Composables/transComposable'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

const { deleteRow, trim, textExcerpt } = sharedComposable()
defineOptions({ layout: AdminLayout })

defineProps(['templates'])
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th>
          {{ trans('Image') }}
        </th>
        <th>
          {{ trans('Name') }}
        </th>
        <th>
          {{ trans('Prompt Type') }}
        </th>
        <th>
          {{ trans('Description') }}
        </th>
        <th>
          {{ trans('Date') }}
        </th>
        <th>
          {{ trans('Status') }}
        </th>
        <th class="text-right">
          {{ trans('Action') }}
        </th>
      </tr>
    </thead>
    <tbody v-if="templates.total">
      <tr v-for="template in templates.data" :key="template.id">
        <td>
          <img v-lazy="template.icon" class="max-w-24" />
        </td>
        <td>
          {{ template.title }}
        </td>
        <td class="capitalize">
          {{ trim(template.prompt_type) }}
        </td>
        <td>
          {{ textExcerpt(template.description, 60) }}
        </td>
        <td>
          {{ moment(template.created_at).format('D MMM, Y') }}
        </td>
        <td>
          <span
            class="badge"
            :class="template.status == 'active' ? 'badge-success' : 'badge-danger'"
          >
            {{ template.status == 'active' ? trans('Active') : trans('Draft') }}
          </span>
        </td>
        <td class="text-end">
          <div class="dropdown dropdown-end" data-placement="bottom-start">
            <button class="dropdown-toggle btn btn-square btn-ghost" type="button">
              <Icon class="h-5 w-5" icon="bx:dots-horizontal-rounded" />
            </button>
            <div class="dropdown-content w-40">
              <ul class="dropdown-list">
                <li class="dropdown-list-item">
                  <Link :href="route('admin.ai-templates.edit', template)" class="dropdown-link">
                    <Icon class="h-5 w-5" icon="fe:edit" />
                    <span>{{ trans('Edit') }}</span>
                  </Link>
                </li>
                <li class="dropdown-list-item">
                  <button
                    type="button"
                    class="dropdown-link"
                    @click="deleteRow(route('admin.ai-templates.destroy', template))"
                  >
                    <Icon class="h-5 w-5" icon="fe:trash" />
                    <span>{{ trans('Delete') }}</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
    <NoDataFound v-else :for-table="true" />
  </table>
</template>
