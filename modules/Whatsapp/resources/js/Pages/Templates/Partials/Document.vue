<script setup>
import AssetInput from "@/Components/Forms/AssetInput.vue"
import TextInput from "@/Components/Dashboard/TextInput.vue"
const props = defineProps({
    document: Object,
})
</script>

<template>
    <div class="space-y-2">
        <AssetInput label="Document" type="document" v-model="document.link" />
        <TextInput v-model="document.caption" label="Caption" placeholder="Caption" />
    </div>
</template>
