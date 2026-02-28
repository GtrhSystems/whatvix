<script setup>
import List from '@/Components/User/QuickReplies/List.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'
import FilterDropdown from '@/Components/Dashboard/FilterDropdown.vue'
defineOptions({ layout: UserLayout })
const props = defineProps(['quickReplies'])
const filterOptions = [
  {
    label: 'Template',
    value: 'message_template'
  },
  {
    label: 'Status',
    value: 'status',
    options: [
      {
        label: 'Active',
        value: 'active'
      },
      {
        label: 'Inactive',
        value: 'inactive'
      }
    ]
  }
]
</script>
<template>
  <FilterDropdown :options="filterOptions" />
  <List module="whatsapp" :quickReplies="props.quickReplies" />
</template>
