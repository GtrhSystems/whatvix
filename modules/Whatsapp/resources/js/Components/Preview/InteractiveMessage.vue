<script setup>
import { computed } from 'vue'

const props = defineProps(['message', 'components'])
const items = computed(
  () =>
    props.components ||
    props.message?.body || {
      type: '',
      header: {},
      body: {},
      footer: {},
      action: {}
    }
)
</script>
<template>
  <div v-if="items?.type == 'button_reply'">
    <p class="text-center font-bold">{{ items.button_reply.title }}</p>
  </div>
  <div v-else class="rounded-md bg-slate-50 p-4 text-black dark:bg-dark-800 dark:text-slate-50">
    <div v-for="(item, key) in items" :key="key">
      <!-- header -->
      <div v-if="key == 'header'">
        <p v-if="item.type == 'text'" class="font-bold">{{ item.text }}</p>
        <img
          v-if="item.type == 'image'"
          :src="item.image.link"
          class="h-36 w-full rounded object-cover"
        />
        <video
          v-if="item.type == 'video'"
          :src="item.video.link"
          class="h-36 w-full rounded object-cover"
        ></video>
        <a
          v-if="item.type == 'document'"
          :href="item.document.link"
          target="_blank"
          rel="noopener noreferrer"
        ></a>
      </div>

      <!-- body -->
      <div v-if="key == 'body'">
        <p class="text-[12px] font-light">
          {{ item.text }}
        </p>
      </div>

      <!-- footer -->
      <div v-if="key == 'footer'">
        <p class="text-[12px] font-light">
          {{ item.text }}
        </p>
      </div>

      <!-- action -->
      <div v-if="key == 'action'" class="mt-4">
        <!-- cta_url -->
        <div v-if="item.name === 'cta_url'">
          <a
            :href="item.parameters.url"
            class="flex w-full items-center justify-center gap-1 border-t py-2 text-xs text-primary-500 hover:text-primary-600"
          >
            <Icon icon="mdi:arrow-top-right-thin-circle-outline" class="size-4" />

            <span>{{ item.parameters.display_text }}</span>
          </a>
        </div>

        <!-- buttons -->
        <div v-if="item.buttons" class="flex flex-col">
          <button
            v-for="(button, index) in item.buttons"
            :key="index"
            class="flex w-full items-center justify-center gap-1 border-t py-2 text-xs text-primary-500 hover:text-primary-600 dark:border-gray-700"
          >
            <Icon icon="basil:reply-outline" class="size-4" />
            <span>{{ button.reply?.title ?? button.title }}</span>
          </button>
        </div>

        <!-- sections -->
        <div v-if="item.sections" class="min-w-52">
          <p class="font-weight-500 py-1 pl-1 text-lg">{{ item.title }}</p>
          <ul>
            <li v-for="(section, sectionIndex) in item.sections" :key="sectionIndex" class="mb-6">
              <p
                class="border-b py-1 text-xs font-semibold text-gray-600 dark:border-gray-600 dark:text-gray-200"
              >
                {{ section.title }}
              </p>
              <ul class="mt-1">
                <li
                  v-for="(row, rowIndex) in section.rows"
                  :key="rowIndex"
                  class="border-b py-3 dark:border-gray-600"
                >
                  <button class="flex w-full items-start justify-between">
                    <div class="text-left">
                      <p class="text-sm leading-4 text-gray-600 dark:text-gray-400">
                        {{ row.title }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400" v-if="row.description">
                        {{ row.description }}
                      </p>
                    </div>
                  </button>
                </li>
              </ul>
            </li>
          </ul>

          <p class="text-center text-[10px] text-gray-500">
            {{ trans('Tap an item to select it') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
