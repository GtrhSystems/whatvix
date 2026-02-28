import { ref } from 'vue'

const file = ref({})

if (Object.keys(file.value)?.length === 0) {
  const data = document.querySelector('meta[name="app-translations"]')?.content

  if (data) {
    file.value = JSON.parse(data)
  }
}

export default function trans(key) {
  if (file.value.hasOwnProperty(key)) {
    return file.value[key]
  }

  return key
}
