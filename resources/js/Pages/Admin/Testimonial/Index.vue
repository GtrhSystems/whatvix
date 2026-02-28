<script setup>
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'

import { useForm, router } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import { ref } from 'vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'

import { useModalStore } from '@/Store/modalStore'
const modalStore = useModalStore()

defineOptions({ layout: AdminLayout })

const { textExcerpt, deleteRow } = sharedComposable()

const props = defineProps(['posts'])
const form = useForm({
  reviewer_name: '',
  short_description: '',
  reviewer_position: '',
  reviewer_avatar: '',
  star: 0,
  comment: ''
})

const editTestimonialForm = ref({})

function openEditTestimonialDrawer(testimonial) {
  editTestimonialForm.value = {
    ...testimonial,
    short_description: testimonial.short_description?.value ?? '',
    _method: 'patch'
  }
  modalStore.open('editModal')
}

const storeTestimonial = () => {
  form.post(route('admin.testimonials.store'), {
    onSuccess: () => {
      form.reset()
      modalStore.close('createModal')
    }
  })
}

const updateTestimonial = () => {
  if (!(editTestimonialForm.value.preview.value instanceof File)) {
    editTestimonialForm.value.preview.value = null
  }

  router.post(
    route('admin.testimonials.update', editTestimonialForm.value.id),
    editTestimonialForm.value,
    {
      onSuccess: () => {
        modalStore.close('editModal')
      }
    }
  )
}
</script>

<template>
  <div class="space-y-6">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Reviewer Name') }}</th>
          <th>{{ trans('Reviewer Position') }}</th>
          <th>{{ trans('Comment') }}</th>
          <th class="text-right">{{ trans('Ratings') }}</th>
          <th class="text-right">{{ trans('Status') }}</th>
          <th class="!text-right">{{ trans('Action') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="post in posts.data" :key="post.id">
          <td>
            <div class="flex">
              <img v-lazy="post.preview?.value" class="avatar rounded-square mr-3" />
              <span>
                {{ textExcerpt(post.title, 30) }}
              </span>
            </div>
          </td>
          <td class="text-left">
            {{ textExcerpt(post.slug, 30) }}
          </td>
          <td class="text-left">
            {{ textExcerpt(post.excerpt?.value ?? '', 50) }}
          </td>
          <td class="text-right">{{ post.lang }} {{ trans('Star') }}</td>
          <td class="text-right">
            <span class="badge" :class="post.status == 1 ? 'badge-success' : 'badge-danger'">
              {{ post.status == 1 ? trans('Active') : trans('Draft') }}
            </span>
          </td>
          <td>
            <div class="dropdown" data-placement="bottom-start">
              <div class="dropdown-toggle">
                <Icon class="w-30 text-lg" icon="bi:three-dots-vertical" />
              </div>
              <div class="dropdown-content w-40">
                <ul class="dropdown-list">
                  <li class="dropdown-list-item">
                    <button @click="openEditTestimonialDrawer(post)" class="dropdown-link">
                      <Icon class="h-6 text-slate-400" icon="material-symbols:edit-outline" />
                      <span>{{ trans('Edit') }}</span>
                    </button>
                  </li>

                  <li class="dropdown-list-item">
                    <button
                      class="dropdown-link"
                      @click="deleteRow('/admin/testimonials/' + post.id)"
                    >
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
    </table>

    <Paginate
      :links="posts.links"
      :currentPage="posts.current_page"
      :from="posts.from"
      :lastPage="posts.last_page"
      :lastPageUrl="posts.last_page_url"
      :nextpageurl="posts.next_page_url"
      :perPage="posts.per_page"
      :prevPageUrl="posts.prev_page_url"
      :to="posts.to"
      :total="posts.total"
    />
  </div>

  <Modal
    state="createModal"
    :header-state="true"
    :header-title="trans('Create Testimonial')"
    :action-btn-text="trans('Create')"
    :action-btn-state="true"
    :action-processing="form.processing"
    @action="storeTestimonial"
  >
    <div class="mb-2">
      <label>{{ trans('Reviewer Name') }}</label>
      <input
        v-model="form.reviewer_name"
        type="text"
        name="reviewer_name"
        maxlength="150"
        class="input"
        required
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Short Note (optional)') }}</label>
      <input
        v-model="form.short_description"
        type="text"
        name="short_description"
        maxlength="150"
        class="input"
        placeholder="Incredible Work!"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Reviewer Position') }}</label>
      <input
        v-model="form.reviewer_position"
        type="text"
        name="reviewer_position"
        class="input"
        required
        placeholder="CEO of Google"
        maxlength="50"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Reviewer Avatar') }}</label>
      <input
        @input="(e) => (form.reviewer_avatar = e.target.files[0])"
        type="file"
        name="reviewer_avatar"
        accept="image/*"
        class="input"
        required=""
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Review Star') }}</label>
      <select v-model="form.star" class="select" name="star">
        <option value="5">{{ trans('5 Star') }}</option>
        <option value="4">{{ trans('4 Star') }}</option>
        <option value="3">{{ trans('3 Star') }}</option>
        <option value="2">{{ trans('2 Star') }}</option>
        <option value="1">{{ trans('1 Star') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Comment') }}</label>
      <textarea
        v-model="form.comment"
        class="textarea h-100"
        maxlength="500"
        name="comment"
        required
      ></textarea>
    </div>
  </Modal>

  <Modal
    state="editModal"
    :header-state="true"
    :header-title="trans('Edit Testimonial')"
    :action-btn-text="trans('Update')"
    :action-btn-state="true"
    :action-processing="editTestimonialForm.processing"
    @action="updateTestimonial"
  >
    <div class="mb-2">
      <label>{{ trans('Reviewer Name') }}</label>
      <input
        v-model="editTestimonialForm.title"
        type="text"
        name="reviewer_name"
        id="reviewer_name"
        maxlength="150"
        class="input"
        required
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Short Note (optional)') }}</label>
      <input
        v-model="editTestimonialForm.short_description"
        type="text"
        name="short_description"
        maxlength="150"
        class="input"
        placeholder="Incredible Work!"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Reviewer Position') }}</label>
      <input
        v-model="editTestimonialForm.slug"
        type="text"
        name="reviewer_position"
        id="reviewer_position"
        class="input"
        required=""
        placeholder="CEO of Google"
        maxlength="50"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Reviewer Avatar') }}</label>
      <input
        @input="(e) => (editTestimonialForm.preview.value = e.target.files[0])"
        type="file"
        name="reviewer_avatar"
        accept="image/*"
        class="input"
      />
    </div>
    <div class="mb-2">
      <label>{{ trans('Review Star') }}</label>
      <select v-model="editTestimonialForm.lang" class="select" name="star" id="star">
        <option value="5">{{ trans('5 Star') }}</option>
        <option value="4">{{ trans('4 Star') }}</option>
        <option value="3">{{ trans('3 Star') }}</option>
        <option value="2">{{ trans('2 Star') }}</option>
        <option value="1">{{ trans('1 Star') }}</option>
      </select>
    </div>
    <div class="mb-2">
      <label>{{ trans('Comment') }}</label>
      <textarea
        :value="editTestimonialForm?.excerpt?.value ?? ''"
        @input="(e) => (editTestimonialForm.excerpt.value = e.target.value)"
        class="textarea h-100"
        maxlength="500"
        name="comment"
        id="comment"
        required
      ></textarea>
    </div>
  </Modal>
</template>
