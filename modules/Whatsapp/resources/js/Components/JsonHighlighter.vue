<script setup>
import '@/../css/json-highlighter.css'

defineProps(['code'])

const syntaxHighlight = (json) => {
  if (typeof json !== 'string') {
    json = JSON.stringify(json, null, 4)
  }
  json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

  return json
    .replace(/("(.*?)")(\s*:\s*)?/g, function (match, key, value, separator) {
      if (separator) {
        return `<span class="json-key">${key}</span>${separator}`
      }
      return `<span class="json-string">${key}</span>`
    })
    .replace(/\b(true|false)\b/g, '<span class="json-boolean">$1</span>')
    .replace(/\b(null)\b/g, '<span class="json-null">$1</span>')
    .replace(/\b(-?\d+(\.\d+)?)\b/g, '<span class="json-number">$1</span>')
}
</script>

<template>
  <div class="json-highlighter" v-html="syntaxHighlight(code)"></div>
</template>
