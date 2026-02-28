<script setup>
import QuickReplyForm from '@/Components/User/QuickReplies/Form.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['platforms', 'shortCodes', 'quickReply'])
</script>
<template>
  <QuickReplyForm
    module="whatsapp-web"
    :platforms="platforms"
    :shortCodes="shortCodes"
    :quickReply="quickReply"
  />
</template>
