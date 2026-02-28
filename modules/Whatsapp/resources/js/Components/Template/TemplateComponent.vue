<script setup>
import { computed, ref } from 'vue'

import AssetModal from '@/Components/Dashboard/AssetModal.vue'
import AssetInput from '@/Components/Forms/AssetInput.vue'
import { useAssetStore } from '@/Store/assetStore'

import SortCodes from '../ShortCodes.vue'
import InputField from '@/Components/Forms/InputField.vue'

const assetStore = useAssetStore()
const props = defineProps({
  components: {
    type: Object,
    default: []
  },
  errors: {
    type: Object,
    default: {}
  }
})

const MPM = ref({
  thumbnail_id: '',
  sections: [
    {
      title: '',
      product_items: [{ product_retailer_id: '' }]
    }
  ]
})

assetStore.loadAssets('image')

const handleDynamicInput = (type, value) => {
  const templateObject = props.components[arrayPosition]

  if (templateObject.format != 'TEXT') {
    templateForm.value[templateObject.type] = value.target.files[0]
  } else {
    templateForm.value[templateObject.type] = value
  }
}

const addNewItem = (type, index = 0) => {
  if (type == 'section') {
    MPM.value.sections.push({
      title: '',
      product_items: [{ product_retailer_id: '' }]
    })
  } else {
    MPM.value.sections[index].product_items.push({
      product_retailer_id: ''
    })
  }
}

const removeItem = (type, index = 0, itemIndex = 0) => {
  if (type == 'section') {
    MPM.value.sections.splice(index, 1)
  } else {
    MPM.value.sections[index].product_items.splice(itemIndex, 1)
  }
}

const getValidationMessage = computed(() => {
  return (matchKey) => {
    if (!props.errors) return ''
    if (props.errors[matchKey]) {
      console.log(props.errors[matchKey])

      return Array.isArray(props.errors[matchKey])
        ? props.errors[matchKey][0]
        : props.errors[matchKey]
    }
    return ''
  }
})

const templateForm = ref({})

const showButtonUrl = (button) => {
  const prefix = button.url.split('{{')[0]
  if (button.example[0].startsWith(prefix)) {
    button.example[0] = ''
  }
  return button.url
}
</script>
<template>
  <div class="space-y-3" v-for="(item, index) in components" :key="index">
    <div
      class="mb-5 rounded-lg border dark:border-dark-700"
      v-if="(item.type == 'HEADER' && item?.example?.header_text) || item?.example?.header_handle"
    >
      <p
        class="rounded-b-none rounded-t-lg border-b bg-slate-100 px-2 py-2 text-center dark:border-dark-700 dark:bg-dark-800"
      >
        {{ trans('Header') }} ({{ item.format }})
      </p>
      <div class="p-3">
        <div v-if="item.format == 'TEXT'" class="mb-3 px-2">
          <div class="flex items-center gap-3">
            <span class="whitespace-nowrap" v-html="`{{1}}`"></span>
            <InputField
              v-model="item.example.header_text[index]"
              :validationMessage="getValidationMessage(`meta.0.example.header_text.0`)"
              placeholder="Enter Text"
            />
          </div>
          <SortCodes @set-short-code="(code) => (item.example.header_text[index] += ` ${code}`)" />
        </div>
        <div v-else-if="item?.example?.header_handle" class="mb-3 px-2">
          <AssetInput
            :type="item.format?.toLowerCase()"
            v-model="item.example.header_handle[index]"
            :error="getValidationMessage(`meta.0.example.header_handle.0`)"
          />
        </div>
        <InputField
          v-else
          @input="(e) => handleDynamicInput(e.target, item)"
          :placeholder="`Enter File Url`"
        />
      </div>
    </div>

    <div
      v-if="item?.type == 'BODY' && item?.example?.body_text[0]"
      class="mb-5 rounded-lg border dark:border-dark-700"
    >
      <p
        class="rounded-b-none rounded-t-lg border-b bg-slate-100 px-2 py-2 text-center dark:border-dark-700 dark:bg-dark-800"
      >
        Body
      </p>
      <div
        v-for="(inputField, exampleIndex) in item.example?.body_text[0]"
        :key="exampleIndex"
        class="form-group mt-3"
      >
        <div class="mb-3 px-2">
          <div class="flex items-center gap-3">
            <span class="whitespace-nowrap" v-html="`{{${exampleIndex + 1}}}`"></span>
            <InputField
              v-model="item.example.body_text[0][exampleIndex]"
              :placeholder="inputField"
              :validationMessage="
                getValidationMessage(`meta.1.example.body_text.0.${exampleIndex}`)
              "
            />
          </div>
          <SortCodes
            @set-short-code="(code) => (item.example.body_text[0][exampleIndex] += ` ${code}`)"
          />
        </div>
      </div>
    </div>

    <div v-if="item?.type == 'BUTTONS'" class="mb-5 rounded-lg border dark:border-dark-700">
      <template v-if="item?.buttons.length">
        <p
          class="rounded-b-none rounded-t-lg border-b bg-slate-100 px-2 py-2 text-center dark:border-dark-700 dark:bg-dark-800"
        >
          {{ trans('Buttons') }}
        </p>
        <div
          class="mt-4 px-3"
          v-for="(buttonItem, buttonIndex) in item?.buttons"
          :key="buttonIndex"
        >
          <!-- quick reply buttons -->
          <div class="mb-2 text-center">
            <button
              v-if="['QUICK_REPLY', 'VOICE_CALL'].includes(buttonItem?.type)"
              class="btn btn-primary"
              type="button"
            >
              {{ buttonItem?.text }}
            </button>
            <a
              v-else-if="buttonItem?.type == 'URL'"
              :href="buttonItem.url"
              target="_blank"
              class="btn btn-primary"
            >
              {{ buttonItem?.text }}
            </a>
          </div>

          <!-- general buttons -->
          <div
            class="mb-2 grid grid-cols-3 items-center gap-3"
            v-if="buttonItem?.example && buttonItem?.example?.length"
          >
            <p class="col-span-1 whitespace-nowrap text-sm font-semibold">
              {{ buttonItem?.text }}
            </p>
            <div class="col-span-2 flex w-full items-center gap-1">
              <div class="text-end" v-if="buttonItem?.type == 'URL'">
                {{ showButtonUrl(buttonItem) }}
              </div>
              <InputField
                v-for="(item, index) in buttonItem?.example"
                :key="index"
                :placeholder="item"
                :customStyle="{
                  'col-span-2': buttonItem?.type != 'URL'
                }"
                v-model="buttonItem.example[index]"
                :validationMessage="
                  getValidationMessage(`meta.3.buttons.${buttonIndex}.example.${index}`)
                "
              />
            </div>
          </div>

          <!-- catalog buttons -->
          <div
            class="mb-2 grid grid-cols-2 items-center gap-3"
            v-if="buttonItem.type === 'CATALOG'"
          >
            <p class="whitespace-nowrap text-sm font-semibold">Thumbnail Product Retailer Id</p>
            <InputField
              placeholder="Example: 2lc20305pt"
              v-model="templateForm.buttons"
              :validationMessage="
                getValidationMessage(
                  `meta.BUTTONS.${buttonItem.type}.thumbnail_product_retailer_id`
                )
              "
            />
          </div>

          <!-- MPM buttons -->
          <div class="" v-if="buttonItem.type === 'MPM'">
            <div class="mb-2 grid grid-cols-2 items-center gap-3">
              <p class="whitespace-nowrap text-sm font-semibold">Thumbnail Product Retailer Id</p>
              <InputField
                placeholder="Example: 2lc20305pt"
                v-model="MPM.thumbnail_id"
                :validationMessage="
                  getValidationMessage(`meta.BUTTONS.${buttonItem.type}.thumbnail_id`)
                "
              />
            </div>
            <div class="flex flex-col gap-2">
              <div class="flex items-center justify-between">
                <label class="label mb-1 capitalize">Section List</label>
                <button @click="addNewItem('section')" type="button" class="btn btn-primary btn-sm">
                  <i class="bx bx-plus"></i> Add Section
                </button>
              </div>
              <div
                v-for="(section, sectionIndex) in MPM.sections"
                :key="sectionIndex"
                class="rounded-lg border-t-2 border-purple-400 p-2"
              >
                <div class="flex items-center justify-between">
                  <label class="text-sm font-medium">Section {{ sectionIndex + 1 }}</label>
                  <button
                    type="button"
                    v-if="sectionIndex != 0"
                    @click="removeItem('section', sectionIndex)"
                    class="text-red-600 hover:text-red-700"
                  >
                    <i class="bx bx-trash"></i>
                  </button>
                </div>

                <div class="my-3">
                  <InputField
                    class="mb-4"
                    placeholder="Section title"
                    v-model="section.title"
                    :validationMessage="
                      getValidationMessage(
                        `meta.BUTTONS.${buttonItem.type}.sections.${sectionIndex}.title`
                      )
                    "
                  />

                  <div
                    v-for="(row, rowIndex) in section.product_items"
                    :key="rowIndex"
                    class="my-3"
                  >
                    <div class="flex gap-2">
                      <InputField
                        placeholder="Product retail id: 2lc20305pt"
                        v-model="row.product_retailer_id"
                        :validationMessage="
                          getValidationMessage(
                            `meta.BUTTONS.${buttonItem.type}.sections.${sectionIndex}.product_items.${rowIndex}.product_retailer_id`
                          )
                        "
                      />

                      <div
                        v-if="rowIndex != 0"
                        @click="removeItem('row', sectionIndex, rowIndex)"
                        class="cursor-pointer text-xl text-red-600 hover:text-red-700"
                      >
                        <i class="bx bx-trash"></i>
                      </div>
                    </div>
                    <div
                      v-if="rowIndex + 1 === section.product_items.length"
                      class="mt-3 flex justify-end py-1"
                    >
                      <button
                        @click="addNewItem('item', sectionIndex)"
                        type="button"
                        class="btn btn-primary"
                      >
                        <i class="bx bx-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
  <AssetModal />
</template>
