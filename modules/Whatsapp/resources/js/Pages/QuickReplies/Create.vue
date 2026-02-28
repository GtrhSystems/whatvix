<script setup>
import QuickReplyForm from '@/Components/User/QuickReplies/Form.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['platforms', 'quickReply'])
</script>
<template>
  <QuickReplyForm
    module="whatsapp"
    :platforms="platforms"
    :shortCodes="['{name}', '{phone}']"
    :quickReply="quickReply"
  />
</template>
