<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import sharedComposable from '@/Composables/sharedComposable'
import NoDataFound from '@/Components/Dashboard/NoDataFound.vue'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })
const props = defineProps(['buttons', 'faqs', 'languages', 'categories'])
const { textExcerpt, deleteRow } = sharedComposable()

const createFrom = useForm({
  question: null,
  answer: null,
  categories: null,
  language: null
})

const editForm = useForm({
  question: null,
  answer: null,
  categories: null,
  language: null,
  id: null
})

function openEditFaqDrawer(faq) {
  editForm.question = faq.title
  editForm.answer = faq.excerpt.value
  editForm.categories = faq.faq_categories[0]?.id || null
  editForm.language = faq.lang
  editForm.id = faq.id
  modalStore.open('editModal')
}

const storeFaq = () => {
  createFrom.post(route('admin.faq.store'), {
    onSuccess: () => {
      createFrom.reset()
      modalStore.close('createModal')
    }
  })
}

const updateFaq = () => {
  router.patch(route('admin.faq.update', editForm.id), editForm, {
    onSuccess: () => {
      editForm.value = {}
      modalStore.close('editModal')
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="table-responsive whitespace-nowrap rounded-primary">
      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('Question') }}</th>
            <th>{{ trans('Answer') }}</th>
            <th class="text-right">{{ trans('Category') }}</th>
            <th class="text-right">{{ trans('Language') }}</th>
            <th class="!text-end">{{ trans('Action') }}</th>
          </tr>
        </thead>
        <tbody v-if="faqs.total">
          <tr v-for="faq in faqs.data" :key="faq.id">
            <td class="text-left">
              {{ textExcerpt(faq.title, 20) }}
            </td>
            <td class="text-left">
              {{ textExcerpt(faq.excerpt.value, 60) }}
            </td>
            <td class="text-left">
              {{ textExcerpt(faq.faq_categories[0]?.title || '', 70) }}
            </td>
            <td class="text-right">
              {{ faq.lang }}
            </td>
            <td>
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
                </div>
                <div class="dropdown-content w-40">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <button type="button" @click="openEditFaqDrawer(faq)" class="dropdown-link">
                        <Icon class="h-6 text-slate-400" icon="material-symbols:edit-outline" />
                        <span>{{ trans('Edit') }}</span>
                      </button>
                    </li>

                    <li class="dropdown-list-item">
                      <button class="dropdown-link" @click="deleteRow('/admin/faq/' + faq.id)">
                        <Icon class="h-6" icon="material-symbols:delete-outline" />
                        <span>{{ trans('Delete') }}</span>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <NoDataFound v-else for-table="true" />
      </table>
    </div>

    <div class="py-4">
      <Paginate :links="faqs.links" />
    </div>
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Create Faq')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="createFrom.processing"
    @action="storeFaq"
  >
    <div class="mb-2">
      <label>{{ trans('Question') }}</label>
      <input type="text" v-model="createFrom.question" maxlength="150" class="input" required="" />
    </div>
    <div class="mb-2">
      <label>{{ trans('Answer') }}</label>
      <textarea
        class="textarea h-100"
        maxlength="500"
        v-model="createFrom.answer"
        required=""
      ></textarea>
    </div>

    <div class="mb-2">
      <label>{{ trans('Select Category') }}</label>
      <select class="select" name="category" v-model="createFrom.categories" required>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.title }}
        </option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Select Language') }}</label>
      <select class="select" name="language" required="" v-model="createFrom.language">
        <option
          v-for="(language, languageKey) in languages"
          :key="language.key"
          :value="languageKey"
        >
          {{ language }}
        </option>
      </select>
    </div>
  </Modal>

  <Modal
    state="editModal"
    :header-state="true"
    :header-title="trans('Edit Faq')"
    :action-btn-text="trans('Update')"
    :action-btn-state="true"
    :action-processing="editForm.processing"
    @action="updateFaq"
  >
    <div class="mb-2">
      <label>{{ trans('Question') }}</label>
      <input
        type="text"
        name="question"
        v-model="editForm.question"
        maxlength="150"
        class="input"
        id="question"
        required=""
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Answer') }}</label>
      <textarea
        class="textarea h-100"
        v-model="editForm.answer"
        maxlength="500"
        name="answer"
        required=""
        id="answer"
      ></textarea>
    </div>

    <div class="mb-2">
      <label>{{ trans('Select Categories') }}</label>
      <select class="select" name="position" v-model="editForm.categories" id="position" required>
        <option
          v-for="category in categories"
          :key="category.id"
          :value="category.id"
          :selected="category.id == editForm.categories"
        >
          {{ category.title }}
        </option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Select Language') }}</label>
      <select class="select" name="language" required="" v-model="editForm.language">
        <option
          v-for="(language, languageKey) in languages"
          :key="language.key"
          :value="languageKey"
        >
          {{ language }}
        </option>
      </select>
    </div>
  </Modal>
</template>
