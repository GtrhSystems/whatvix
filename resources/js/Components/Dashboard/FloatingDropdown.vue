<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  btnType: {
    type: String,
    default: 'text'
  },
  btnText: {
    type: String,
    default: 'Toggle'
  },
  iconName: {
    type: String,
    default: 'bx:dots-horizontal-rounded'
  },
  btnClass: {
    type: String,
    default: 'btn'
  },
  iconClass: {
    type: String,
    default: 'text-2xl'
  },
  position: {
    type: String,
    default: 'right'
  },
  activeClass: {
    type: String,
    default: 'text-primary-500'
  }
})

const dropdown = ref(false)
const dropdownBtn = ref(null)
const dropdownContent = ref(null)
const dropdownStyles = ref({
  position: 'fixed',
  top: '0px',
  left: '0px',
  zIndex: 999
})

const toggleDropdown = () => {
  dropdown.value = !dropdown.value
  if (dropdown.value) nextTick(() => updateDropdownPosition())
}

const updateDropdownPosition = () => {
  if (dropdownBtn.value && dropdownContent.value) {
    const btnRect = dropdownBtn.value.getBoundingClientRect()
    const contentRect = dropdownContent.value.getBoundingClientRect()
    const spaceBottom = window.innerHeight - btnRect.bottom

    let left = btnRect.left
    let top = btnRect.bottom

    left = btnRect.left - contentRect.width + btnRect.width

    if (spaceBottom < contentRect.height) {
      top = btnRect.top - contentRect.height
    }

    if (left < 0) left = 0
    if (left + contentRect.width > window.innerWidth) {
      left = window.innerWidth - contentRect.width
    }

    if (top < 0) top = 0

    dropdownStyles.value = {
      position: 'fixed',
      top: `${top}px`,
      left: `${left}px`,
      zIndex: 999
    }
  }
}

const outsideClick = (event) => {
  if (
    dropdown.value &&
    !dropdownContent.value.contains(event.target) &&
    !dropdownBtn.value.contains(event.target) &&
    event.target !== dropdownBtn.value
  ) {
    dropdown.value = false
  }
}

const handleResize = () => {
  if (dropdown.value) {
    updateDropdownPosition()
  }
}

onMounted(() => {
  document.addEventListener('click', outsideClick)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  document.removeEventListener('click', outsideClick)
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div class="relative flex max-w-min flex-col">
    <button
      ref="dropdownBtn"
      type="button"
      @click="toggleDropdown"
      class="max-w-max"
      :class="[dropdown ? activeClass : '', btnClass]"
    >
      <template v-if="btnText && btnType === 'text'"> {{ btnText }}</template>
      <template v-else-if="btnText && btnType === 'icon'">
        <Icon :class="iconClass" :icon="iconName" />
      </template>
      <slot v-else-if="btnType === 'slot'" name="btnContent">{{ btnText }}</slot>
    </button>

    <Teleport to="body">
      <Transition name="slide-bottom">
        <div
          v-if="dropdown"
          ref="dropdownContent"
          :style="dropdownStyles"
          class="mt-1 min-w-max overflow-hidden whitespace-nowrap rounded-md border bg-slate-50 shadow-lg dark:border-secondary-700 dark:bg-dark-800"
        >
          <slot>
            <p v-for="i in 10" :key="i">{{ `Dropdown Contents ${i}` }}</p>
          </slot>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
