<script setup>
import AssetInput from "@/Components/Forms/AssetInput.vue"
import TextInput from "@/Components/Dashboard/TextInput.vue"
const props = defineProps({
    video: Object,
})
</script>

<template>
    <div class="space-y-2">
        <AssetInput label="Video" type="video" v-model="video.link" />
        <TextInput v-model="video.caption" label="Caption" placeholder="Caption" />
    </div>
</template>
