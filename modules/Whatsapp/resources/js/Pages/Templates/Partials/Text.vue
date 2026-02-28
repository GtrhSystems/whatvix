<script setup>
import ShortCodes from '@whatsapp/Components/ShortCodes.vue'
const props = defineProps({
  text: Object
})
</script>

<template>
  <div>
    <textarea class="textarea" v-model="text.body" placeholder="enter message"></textarea>
    <ShortCodes v-model="text.body" class="mb-2" />

    <label for="preview_url" class="label">
      <input type="checkbox" id="preview_url" class="checkbox" v-model="text.preview_url" />
      {{ trans('Preview Url') }}</label
    >
  </div>
</template>
