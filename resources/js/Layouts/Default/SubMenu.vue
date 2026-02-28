<script setup>
import SubMenu from '@/Layouts/Default/SubMenu.vue'
const props = defineProps({
  item: {
    type: Object
  }
})
const isExternalAuth = (href) => {
  return ['/login', '/register'].some((path) => href.endsWith(path))
}
</script>

<template>
  <a
    class="nav-link dropdown-toggle"
    href="#"
    role="button"
    data-bs-toggle="dropdown"
    data-bs-auto-close="outside"
    aria-expanded="false"
  >
    {{ item.text }}
  </a>
  <ul class="dropdown-menu">
    <li
      class="dropdown-item"
      v-for="child in item.children"
      :key="child.id"
      :class="{ dropdown: child.children?.length > 0 }"
    >
      <SubMenu v-if="child.children?.length > 0" :children="child.children" />

      <component
        v-else
        :is="isExternalAuth(item.href) || child.target == '_top' ? 'a' : 'Link'"
        :href="sanitizeUrl(child.href ?? '#')"
        :target="child.target"
      >
        {{ child.text }}
      </component>
    </li>
  </ul>
</template>