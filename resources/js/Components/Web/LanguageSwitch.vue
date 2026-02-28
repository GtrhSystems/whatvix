<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const isOpen = ref(false)

const selectContainer = ref(null)
const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const outsideClick = (event) => {
  if (isOpen.value && !selectContainer.value.contains(event.target)) {
    isOpen.value = false
  }
}
onMounted(() => {
  document.addEventListener('click', outsideClick)
})

onUnmounted(() => {
  document.removeEventListener('click', outsideClick)
})
</script>

<template>
  <div class="language-select position-relative mt-40">
    <div class="current me-2" @click.stop="toggleDropdown">
      {{ $page.props.languages[$page.props.locale] }}
    </div>

    <ul
      class="position-absolute style-none mt-5 rounded bg-white py-1"
      ref="selectContainer"
      v-if="isOpen"
    >
      <li
        class="text-black"
        :class="{
          'selected focus': key === $page.props.locale
        }"
        v-for="(language, key) in $page.props.languages"
        :key="key"
      >
        <Link
          as="button"
          :href="route('set-locale', key)"
          method="patch"
          class="dropdown-btn w-100 text-start"
        >
          {{ trans(language) }}
        </Link>
      </li>
    </ul>
  </div>
</template>
<style>
.language-select .current {
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 10px;
  background: #fff;
  padding: 10px 70px 10px 20px;
  font-size: 20px;
  color: #000;
}
.language-select ul {
  width: 97%;
}
.language-select ul li {
  list-style: none;
  padding: 2px 15px;
  cursor: pointer;
}
.language-select ul li:hover {
  background: #f6f6f6;
}
</style>
