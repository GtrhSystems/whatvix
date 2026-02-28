<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  templateData: {
    type: Array,
    default: []
  },
  updatedTemplateSecret: {
    type: Object,
    default: {
      HEADER: null,
      BODY: [],
      BUTTONS: {
        URL: null,
        COPY_CODE: null
      }
    }
  }
})

const placeValue = computed(() => {
  return (item, subItem = null) => {
    if (item && item.type == 'BODY') {
      let text = item.text
      if (
        item &&
        props.updatedTemplateSecret &&
        props.updatedTemplateSecret[item.type] &&
        !props.updatedTemplateSecret[item.type]?.length
      )
        return text
      props.updatedTemplateSecret[item.type]?.map((value, idx) => {
        text = text.replace(`{{${idx + 1}}}`, value)
      })
      return text
    } else if (item?.type == 'BUTTONS' && subItem) {
      if (!props.updatedTemplateSecret[item.type]) return ''
      if (!props.updatedTemplateSecret[item.type][subItem.type]) return subItem[subItem.type] ?? ''
    } else if (item && item.type == 'HEADER') {
      let text = item.text
      if (!props.updatedTemplateSecret[item.type]) return text
      return text.replace(`{{${1}}}`, props.updatedTemplateSecret[item.type])
    }
    return ''
  }
})
</script>
<template>
  <div class="rounded-md bg-slate-100 p-4 dark:bg-dark-900">
    <template v-for="(item, index) in templateData" :key="index">
      <template v-if="item.type === 'HEADER'">
        <template v-if="item.format === 'TEXT' && item?.text">
          <p class="mb-2 font-bold" id="headerText">
            {{ placeValue(item) }}
          </p>
        </template>
        <template v-else-if="item.format === 'IMAGE' && item?.example?.header_handle[0]">
          <img
            :src="item?.example?.header_handle[0]"
            class="h-36 w-full rounded object-cover"
            alt=""
          />
        </template>
        <template v-else-if="item.format === 'DOCUMENT' && item?.example?.header_handle[0]">
          <a
            :href="item?.example?.header_handle[0]"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center justify-between gap-3 rounded bg-gray-100 p-3"
          >
            <i class="bx bx-file"></i>
            <span class="text-cyan-600">Click</span>
          </a>
        </template>
      </template>

      <p v-if="item.type === 'BODY'" id="bodyText" class="mb-2 py-1 text-[12px] font-light">
        {{ placeValue(item) }}
      </p>

      <template v-if="item.type === 'FOOTER' && item?.text">
        <p class="text-[11px] font-light">
          {{ item.text }}
        </p>
      </template>

      <template v-if="item?.type === 'BUTTONS' && item?.buttons?.length">
        <ul class="border-t dark:border-dark-700">
          <li
            v-for="(button, key) in item?.buttons"
            :key="key"
            :class="{
              'border-b dark:border-dark-700': key !== item?.buttons.length - 1
            }"
            class="px-1 py-2 text-center"
          >
            <template v-if="button.type === 'URL'">
              <a
                :href="sanitizeUrl(placeValue(item, button))"
                class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
              >
                <i class="fa-solid fa-pen-to-square"></i>
                {{ button?.text }}
              </a>
            </template>
            <template v-else-if="button.type === 'PHONE_NUMBER'">
              <a
                href=""
                class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
              >
                <i class="fa-solid fa-phone"></i>
                {{ button?.text }}
              </a>
            </template>
            <template v-else-if="button.type === 'COPY_CODE'">
              <a
                href=""
                class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
              >
                {{ button?.text }}
              </a>
            </template>
            <template v-else>
              <a
                href=""
                class="w-full text-center text-sm font-normal text-primary-400 hover:text-primary-600"
              >
                {{ button?.text }}
              </a>
            </template>
          </li>
        </ul>
      </template>
    </template>
  </div>
</template>

<style></style>
