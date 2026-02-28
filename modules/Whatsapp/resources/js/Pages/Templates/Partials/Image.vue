<script setup>
import AssetInput from "@/Components/Forms/AssetInput.vue"
import TextInput from "@/Components/Dashboard/TextInput.vue"
const props = defineProps({
    image: Object,
})
</script>

<template>
    <div class="space-y-2">
        <AssetInput label="Image" type="image" v-model="image.link" />
        <TextInput v-model="image.caption" label="Caption" placeholder="Caption" />
    </div>
</template>
