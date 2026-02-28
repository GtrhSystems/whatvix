<template>
  <div class="relative w-full">
    <div
      ref="editableDiv"
      :contenteditable="!disabled"
      class="input-content styled-scrollbar max-h-24 min-h-16 overflow-y-auto rounded p-3 focus:border-purple-500 focus:outline-none dark:text-dark-300"
      :class="{ 'cursor-not-allowed ': disabled }"
      @input="handleInput"
      @keydown="handleKeydown"
      v-html="processedContent"
      v-bind="$attrs"
    ></div>
    <div
      v-if="!props.modelValue"
      class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400"
    >
      {{ placeholder }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
defineOptions({
  inheritAttrs: false
})
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Type your text here...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  autofocus: {
    type: Boolean,
    default: false
  }
})
onMounted(() => {
  if (props.autofocus) {
    editableDiv.value.focus()
  }
})
const emit = defineEmits(['update:modelValue'])
const editableDiv = ref(null)
const isUpdating = ref(false)
const lastCaretPosition = ref(null)

const processedContent = computed(() => {
  return props.modelValue
})

const getCaretPosition = () => {
  const selection = window.getSelection()
  if (!selection.rangeCount) return null

  const range = selection.getRangeAt(0)
  const preCaretRange = range.cloneRange()
  preCaretRange.selectNodeContents(editableDiv.value)
  preCaretRange.setEnd(range.endContainer, range.endOffset)
  return preCaretRange.toString().length
}

const setCaretPosition = (position) => {
  const selection = window.getSelection()
  const range = document.createRange()
  let currentPos = 0
  let found = false

  function traverseNodes(node) {
    if (found) return
    if (node.nodeType === Node.TEXT_NODE) {
      const nodeLength = node.textContent.length
      if (currentPos + nodeLength >= position) {
        range.setStart(node, position - currentPos)
        range.setEnd(node, position - currentPos)
        found = true
        return
      }
      currentPos += nodeLength
    } else {
      for (const childNode of node.childNodes) {
        traverseNodes(childNode)
      }
    }
  }

  traverseNodes(editableDiv.value)

  if (found) {
    selection.removeAllRanges()
    selection.addRange(range)
  }
}

const handleInput = () => {
  if (isUpdating.value) return
  lastCaretPosition.value = getCaretPosition()
  const text = editableDiv.value.innerText
  emit('update:modelValue', text)
}

const handleKeydown = (e) => {
  if (e.key === 'Backspace') {
    const position = getCaretPosition()
    lastCaretPosition.value = position - 1
  } else if (e.key === 'Delete') {
    lastCaretPosition.value = getCaretPosition()
  }
}

watch(
  () => props.modelValue,
  async () => {
    if (editableDiv.value && !isUpdating.value) {
      isUpdating.value = true

      editableDiv.value.innerHTML = processedContent.value

      await nextTick()

      if (lastCaretPosition.value !== null) {
        setCaretPosition(lastCaretPosition.value)
      }

      isUpdating.value = false
    }
  }
)
</script>

<style>
.input-content {
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.5;
}

.highlighted-text {
  color: theme('colors.primary.500');
  cursor: pointer;
}

.input-content:empty::before {
  display: none;
}
</style>
