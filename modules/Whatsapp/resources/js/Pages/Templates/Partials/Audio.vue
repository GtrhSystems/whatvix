<script setup>
import AssetInput from "@/Components/Forms/AssetInput.vue"
import TextInput from "@/Components/Dashboard/TextInput.vue"
const props = defineProps({
    audio: Object,
})
</script>

<template>
    <div class="space-y-2">
        <AssetInput label="Audio" type="audio" v-model="audio.link" />
    </div>
</template>
