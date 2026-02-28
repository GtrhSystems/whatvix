<script setup>
const { message } = defineProps(['message'])
</script>

<template>
    <p class="bg-white p-3 italic text-gray-500">
        {{ `unsupported message type:` }} "<span class="font-semibold"> {{ message.type }}</span>"
    </p>
</template>