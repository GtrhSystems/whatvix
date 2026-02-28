rgb(255, 0, 13)
<script setup>
const props = defineProps({
  links: [Object, Array],
  preserveState: false,
  preserveScroll: false
})
</script>

<template>
  <ul class="pagination-two style-none d-flex align-items-center pt-20" v-if="links.length > 3">
    <li v-for="(link, key) in links" :key="key">
      <a
        v-if="link.url === null"
        :key="key"
        :class="{ current: link.active }"
        v-html="link.label.includes('Next') ? '&raquo;' : '&laquo;'"
      ></a>
      <Link
        v-else
        :preserve-state="preserveState"
        :preserve-scroll="preserveScroll"
        :key="`link-${key}`"
        :class="{ current: link.active }"
        :href="link.url"
      >
        <span class="text-nowrap" v-html="link.label"></span>
      </Link>
    </li>
  </ul>
</template>
