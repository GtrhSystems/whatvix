<script setup>
import { computed } from 'vue'

const props = defineProps(['message', 'templateData'])

const replaceExampleValues = (item) => {
  const text = item.text ?? ''

  const values = item.example?.header_text ?? item.example?.body_text?.[0] ?? []

  if (text.includes('{{') && text.includes('}}') && values.length) {
    return values.reduce((acc, value, index) => {
      return acc.replace(`{{${index + 1}}}`, value)
    }, text)
  }

  return text
}

const components = computed(() => {
  return props.templateData || props.message?.meta || []
})
</script>
<template>
  <div class="m-1 rounded-md bg-slate-100 p-4 text-black dark:bg-dark-800 dark:text-white">
    <div v-for="(item, index) in components" :key="index">
      <!-- header -->
      <div v-if="item.type?.toLowerCase() == 'header'">
        <template v-if="item.format?.toLowerCase() == 'text' && item?.text">
          <p class="mb-2 font-bold" id="headerText">
            {{ replaceExampleValues(item) }}
          </p>
        </template>
        <template
          v-else-if="item.format.toLowerCase() == 'image' && item?.example?.header_handle[0]"
        >
          <img
            :src="item?.example?.header_handle[0]"
            class="h-36 w-full rounded object-cover"
            alt=""
          />
        </template>
        <template
          v-else-if="item.format.toLowerCase() == 'document' && item?.example?.header_handle[0]"
        >
          <a
            :href="item"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center justify-between gap-3 rounded bg-gray-100 p-3 dark:bg-dark-700"
          >
            <i class="bx bx-file"></i>
            <span class="text-cyan-600">{{ trans('Click') }}</span>
          </a>
        </template>
      </div>

      <!-- body -->
      <p v-if="item.type?.toLowerCase() == 'body'" id="bodyText" class="mb-2 py-1 text-[12px]">
        {{ replaceExampleValues(item) }}
      </p>

      <!-- footer -->
      <div v-if="item.type?.toLowerCase() == 'footer' && item?.text">
        <p class="text-[11px]">
          {{ replaceExampleValues(item) }}
        </p>
      </div>

      <!-- buttons -->
      <div v-if="item?.type == 'buttons' && item?.buttons?.length">
        <ul class="mt-3 border-t dark:border-dark-700">
          <li
            v-for="(button, key) in item?.buttons"
            :key="key"
            :class="{
              'border-b': key !== item?.buttons.length - 1
            }"
            class="px-1 py-2 text-center dark:border-dark-700"
          >
            <a
              :href="item"
              v-if="button.type == 'URL'"
              class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
            >
              <i class="fa-solid fa-pen-to-square"></i>
              {{ button?.text }}
            </a>
            <a
              v-else-if="button.type == 'phone_number'"
              href="#"
              class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
            >
              <i class="fa-solid fa-phone"></i>
              {{ button?.text }}
            </a>
            <a
              v-else-if="button.type == 'copy_code'"
              href="#"
              class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
            >
              {{ button?.text }}
            </a>
            <a
              v-else
              href="#"
              class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
            >
              {{ button?.text }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
