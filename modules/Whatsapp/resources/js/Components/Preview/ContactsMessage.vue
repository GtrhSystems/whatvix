<script setup>
const { message } = defineProps(['message'])
</script>

<template>
  <div
    v-for="contact in message.meta?.contacts ?? message.body ?? []"
    :key="contact"
    class="flex items-center gap-3 rounded p-1"
  >
    <div class="flex gap-1">
      <Icon icon="mdi:account" class="size-5" />
      <div>
        <p class="text-sm font-semibold">
          {{ contact.name?.formatted_name }}
        </p>
        <div
          v-for="phone_number in contact.phones"
          :key="phone_number.phone"
          class="-ml-0.5 -mt-0.5"
        >
          <a class="text-xs" :href="`tel:${sanitizeHtml(phone_number.phone)}`">
            {{ phone_number.phone }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
