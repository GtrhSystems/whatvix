<script setup>
const props = defineProps({
    location: Object,
})
</script>

<template>
    <div class="flex gap-2 flex-col">
        <div class="grid grid-cols-2 gap-2">
            <input type="text" v-model="location.name" class="input" placeholder="Name" />
            <input type="text" v-model="location.address" class="input" placeholder="Address" />
            <input type="text" v-model="location.latitude" class="input" placeholder="Latitude" />
            <input type="text" v-model="location.longitude" class="input" placeholder="Longitude" />
        </div>
    </div>
</template>
