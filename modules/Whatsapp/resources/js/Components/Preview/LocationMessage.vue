<script setup>
const { message } = defineProps(['message'])
</script>

<template>
    <iframe
        :src="`https://maps.google.com/maps?q=${message.meta?.location?.latitude ?? message.body?.latitude},${message.meta?.location?.longitude ?? message.body?.longitude}&output=embed`"
        class="h-32 w-full rounded"></iframe>
</template>