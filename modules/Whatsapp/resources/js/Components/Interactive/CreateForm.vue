<script setup>
import ShortCodes from '../ShortCodes.vue'
import InputField from '../InputField.vue'
import Preview from '../Preview/InteractiveMessage.vue'
import AssetInput from '@/Components/Forms/AssetInput.vue'
const props = defineProps({
  meta: Object,
  errors: {
    type: Object,
    default: []
  },
  preview: {
    type: Boolean,
    default: true
  }
})

const headerTypes = ['text', 'image', 'video', 'document']

const randomId = () => {
  return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
}

const addNewItem = (type, component) => {
  if (type == 'row') {
    component.rows.push({
      id: '',
      title: '',
      description: ''
    })
  } else if (type == 'section') {
    component.sections.push({
      rows: [
        {
          id: randomId(),
          title: '',
          description: ''
        }
      ]
    })
  } else if (type == 'product_items') {
    component.product_items.push({
      product_retailer_id: ''
    })
  }
}

const removeItem = (type, component, rowIndex = null) => {
  if (type == 'section') {
    component.sections.splice(rowIndex, 1)
  } else if (type == 'row') {
    component.rows.splice(rowIndex, 1)
  } else if (type == 'product_items') {
    component.product_items.splice(rowIndex, 1)
  }
}

const addNewReplyButton = () => {
  let buttons = props.meta.action.buttons ?? []
  if (buttons.length >= 3) {
    return
  }
  buttons.push({
    type: 'reply',
    reply: {
      id: randomId(),
      title: ''
    }
  })
}

const removeReplyButton = (id) => {
  let buttons = props.meta.action.buttons ?? []
  if (buttons.length <= 1) {
    return
  }
  props.meta.action.buttons = props.meta.action.buttons.filter((button) => button.reply.id != id)
}

const setHeader = (component, type) => {
  component[component.type] = undefined
  component['type'] = type

  if (type == 'text') {
    component.text = ''
  } else {
    component[type] = {
      link: ''
    }
  }
}
</script>
<template>
  <div v-for="(component, key) in props.meta" :key="key" class="col-span-full w-full md:col-span-2">
    <!-- card Item -->
    <div
      v-if="!['type', 'schedule_timezone', 'schedule_timestamp'].includes(key)"
      class="mb-5 rounded-lg border dark:border-dark-700"
    >
      <!-- Card Title -->
      <div
        class="rounded-b-none rounded-t-lg border-b bg-slate-100 px-2 py-2 text-center capitalize dark:border-dark-700 dark:bg-dark-800"
      >
        {{ key }}
      </div>

      <!-- Card Body -->
      <div class="space-y-2 p-2" v-if="key == 'header'">
        <div class="w-full">
          <label class="label mb-1 capitalize">Type</label>
          <select class="select" @change="(e) => setHeader(component, e.target.value)">
            <option v-for="(type, index) in headerTypes" :key="index" :value="type">
              {{ type }}
            </option>
          </select>
        </div>
        <div class="align-end flex flex-col justify-between gap-1">
          <label for="">{{ component.type }}</label>
          <div v-if="component.type == 'text'">
            <InputField
              v-model="component.text"
              placeholder="Enter text value"
              :validation-message="errors[`meta.header.text`]"
              max-length="60"
            />
            <ShortCodes v-model="component.text" />
          </div>
          <AssetInput :type="component.type" v-else v-model="component[component.type]['link']" />
        </div>
      </div>

      <!-- body -->
      <div class="space-y-2 p-2" v-if="key == 'body'">
        <div class="align-end flex flex-col justify-between gap-1">
          <textarea v-model="component.text" class="input" placeholder="Enter text message" />
          <p class="text-sm text-red-600" v-if="errors[`meta.body.text`]">
            {{ errors[`meta.body.text`] }}
          </p>
          <ShortCodes v-model="component.text" />
        </div>
      </div>

      <!-- footer -->
      <div class="space-y-2 p-2" v-if="key == 'footer'">
        <div class="align-end flex flex-col justify-between gap-1">
          <InputField
            v-model="component.text"
            max-length="60"
            placeholder="Enter text value"
            :validation-message="errors[`meta.footer.text`]"
          />
          <ShortCodes v-model="component.text" />
        </div>
      </div>

      <!-- Action -->
      <div v-if="key === 'action'" class="mt-3 space-y-3 p-2">
        <div v-if="meta.type == 'button'" class="p-1">
          <button
            type="button"
            v-if="meta.action.buttons.length < 3"
            @click="addNewReplyButton()"
            class="btn btn-primary"
          >
            +
          </button>
          <div v-for="(button, btIndex) in component.buttons" :key="btIndex" class="mb-2">
            <label for="">{{ trans('Button') }} {{ btIndex + 1 }}</label>
            <div class="flex gap-1">
              <input
                type="text"
                v-model="button.reply.title"
                class="input"
                placeholder="enter button text"
              />
              <button @click="removeReplyButton(button.reply.id)" class="btn btn-danger">-</button>
            </div>
          </div>
        </div>

        <div v-if="meta.type == 'cta_url'" class="p-1">
          <div class="mb-2">
            <label for=""> {{ trans('Button Text') }}</label>
            <input type="text" v-model="component.parameters.display_text" class="input" />
          </div>

          <div class="mb-2">
            <label for=""> {{ trans('Button Url') }}</label>
            <input type="text" v-model="component.parameters.url" class="input" />
          </div>
        </div>

        <div v-if="meta.type == 'catalog_message'">
          <div class="mb-2 grid grid-cols-2 items-center gap-3">
            <p class="whitespace-nowrap text-sm font-semibold">
              {{ trans('Thumbnail Product Retailer Id') }}
            </p>
            <InputField v-model="component.thumbnail_product_retailer_id" placeholder="ex: text" />
          </div>
        </div>

        <div v-if="meta.type == 'product'">
          {{ trans('') }}
          <div class="mb-4 grid grid-cols-2 items-center gap-3">
            <p class="whitespace-nowrap text-sm font-semibold">Enter Catalog Id (*)</p>
            <InputField v-model="component.catalog_id" placeholder="ex: 2lc20305pt" />
            <div class="col-span-full text-sm">
              {{
                trans(`Unique identifier of the Facebook catalog linked to your WhatsApp Business Account.
              This ID can be retrieved via the Meta Commerce Manager.`)
              }}
            </div>
          </div>
          <div class="grid grid-cols-2 items-center gap-3 pb-3">
            <p class="whitespace-nowrap text-sm font-semibold">
              {{ trans('Product Retailer Id') }} (*)
            </p>
            <InputField
              v-model="component.product_retailer_id"
              placeholder="product-SKU-in-catalog"
            />
            <div class="col-span-full text-sm">
              {{
                trans(`To get this ID go to Meta Commerce Manager and select your Meta Business account. You
              will see a list of shops connected to your account. Click the shop you want to use. On
              the left-side panel, click Catalog > Items, and find the item you want to mention. The
              ID for that item is displayed under the item's name.`)
              }}
            </div>
          </div>
        </div>

        <div v-if="meta.type == 'list'">
          <div class="w-full">
            <InputField
              label="List Button Text"
              v-model="component.button"
              placeholder="Enter action title"
            />
          </div>

          <div class="mt-3 flex flex-col gap-2">
            <div class="flex items-center justify-between">
              <label class="label capitalize">{{ trans('Section List') }}</label>
              <button
                type="button"
                @click="addNewItem('section', component)"
                class="btn btn-primary btn-sm"
              >
                <i class="bx bx-plus"></i>
                {{ trans('Add Section') }}
              </button>
            </div>
            <div
              v-for="(section, sectionIndex) in component.sections"
              :key="sectionIndex"
              class="space-y-1 rounded-lg border-t-2 border-t-purple-600 p-2"
            >
              <div class="flex items-center justify-between">
                <label class="label mb-1"
                  >{{ trans('') }} {{ trans('Section') }} {{ sectionIndex + 1 }} Title
                </label>
                <div
                  v-if="sectionIndex != 0"
                  @click="removeItem('section', component, sectionIndex)"
                  class="cursor-pointer text-xl text-red-600 hover:text-red-700"
                >
                  <i class="bx bx-trash"></i>
                </div>
              </div>
              <div class="w-full">
                <InputField v-model="section.title" placeholder="Enter section title" />
              </div>
              <div class="flex flex-col gap-3">
                <div
                  v-for="(row, rowIndex) in section.rows"
                  :key="rowIndex"
                  class="flex flex-col gap-3"
                >
                  <div>
                    <div class="grid grid-cols-2 gap-3">
                      <div class="w-full">
                        <InputField
                          :label="`Item ${rowIndex + 1} Title`"
                          v-model="row.title"
                          placeholder="Enter title"
                        />
                      </div>
                      <div class="w-full">
                        <div class="flex items-center justify-between">
                          <label class="label mb-1 capitalize"
                            >{{ trans('Item') }}
                            {{ rowIndex + 1 }}
                            {{ trans('Description') }}</label
                          >
                          <div
                            v-if="rowIndex != 0"
                            @click="removeItem('row', section, rowIndex)"
                            class="cursor-pointer text-xl text-red-600 hover:text-red-700"
                          >
                            <i class="bx bx-trash"></i>
                          </div>
                        </div>
                        <InputField v-model="row.description" placeholder="Enter description" />
                      </div>
                    </div>
                    <div v-if="rowIndex + 1 === section.rows.length" class="mt-2 flex justify-end">
                      <button
                        type="button"
                        @click="addNewItem('row', section)"
                        class="btn btn-primary btn-xs"
                      >
                        <i class="bx bx-plus"></i>
                        {{ trans('Add Row') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="meta.type == 'product_list'">
          <div class="w-full">
            <label class="label mb-1 capitalize">{{ trans('Enter Catalog Id') }}</label>
            <input
              type="text"
              class="input"
              placeholder="Example: t-56fdofer"
              v-model="component.catalog_id"
            />
          </div>
          <div>
            <div class="flex flex-col gap-3">
              <div class="mt-2 flex items-center justify-between">
                <label class="label mb-1 capitalize">{{ trans('Section List') }}</label>
                <button
                  type="button"
                  @click="addNewItem('section', component)"
                  class="btn btn-primary btn-sm"
                >
                  <i class="bx bx-plus"></i>
                  {{ trans('Add Section') }}
                </button>
              </div>
              <div
                v-for="(section, sectionIndex) in component.sections"
                :key="sectionIndex"
                class="space-y-1 rounded-lg border-t-2 border-t-purple-600 p-2"
              >
                <div class="mb-6">
                  <div class="flex items-center justify-between">
                    <label class="label mb-1"
                      >{{ trans('') }} {{ trans('Section') }} {{ sectionIndex + 1 }}
                      {{ trans('Title') }}
                    </label>
                    <div
                      v-if="sectionIndex != 0"
                      @click="removeItem('section', component, sectionIndex)"
                      class="cursor-pointer text-xl text-red-600 hover:text-red-700"
                    >
                      <i class="bx bx-trash"></i>
                    </div>
                  </div>
                  <input
                    type="text"
                    class="input"
                    placeholder="product-SKU-in-catalog"
                    v-model="section.title"
                  />
                </div>
                <div class="flex flex-col gap-4">
                  <div
                    v-for="(row, rowIndex) in section.product_items"
                    :key="rowIndex"
                    class="flex flex-col gap-3"
                  >
                    <div>
                      <div class="w-full">
                        <div class="flex items-center justify-between">
                          <label class="label mb-1 capitalize"
                            >{{ trans('Product Retailer Id') }}
                            {{ rowIndex + 1 }}
                          </label>
                          <div
                            v-if="rowIndex != 0"
                            @click="removeItem('product_items', section, rowIndex)"
                            class="cursor-pointer text-xl text-red-600 hover:text-red-700"
                          >
                            <i class="bx bx-trash"></i>
                          </div>
                        </div>
                        <input
                          type="text"
                          class="input"
                          placeholder="product-SKU-in-catalog"
                          v-model="row.product_retailer_id"
                        />
                      </div>
                      <div
                        v-if="rowIndex + 1 === section.product_items.length"
                        class="mt-3 flex justify-end"
                      >
                        <button
                          @click="addNewItem('product_items', section)"
                          type="button"
                          class="btn btn-primary btn-sm"
                        >
                          <i class="bx bx-plus"></i>
                          {{ trans('Add Row') }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
