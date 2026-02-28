<script setup>
defineProps({
  message: {
    type: String,
    default: 'No data found'
  },
  forTable: {
    type: [Boolean, String],
    default: false
  }
})
</script>

<template>
  <tbody v-if="forTable">
    <tr>
      <td colspan="10">
        <div class="mb-8 mt-5 flex flex-col items-center justify-center">
          <img src="/assets/images/no-data.svg" class="my-3 h-16" alt="" />
          <p class="text-gray-500">{{ message }}</p>
        </div>
      </td>
    </tr>
  </tbody>

  <div v-else class="card mb-8 mt-4 flex flex-col items-center justify-center py-5">
    <img src="/assets/images/no-data.svg" class="my-3 h-16" alt="no-data" />
    <p class="text-gray-500">{{ message }}</p>
  </div>
</template>
