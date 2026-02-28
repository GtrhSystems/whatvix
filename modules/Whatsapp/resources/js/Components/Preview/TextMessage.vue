<script setup>
const { message } = defineProps(['message'])
</script>

<template>
  <p :class="{ 'text-gray-600 dark:text-white': message.direction == 'out' }">
    {{ message.body?.body ?? message.body?.text ?? message.body }}
  </p>
</template>
