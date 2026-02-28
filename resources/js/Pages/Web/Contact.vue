<script setup>
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import { useForm } from '@inertiajs/vue3'
defineOptions({ layout: DefaultLayout })
defineProps(['contact_page'])

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const submit = () => {
  form.post('/contact-us', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}
const origin = window.location.origin
</script>

<template>
  <!--
		=====================================================
			Map Banner
		=====================================================
		-->
  <div class="map-banner-one mt-140 sm-mt-80">
    <div class="gmap_canvas h-100 w-100">
      <iframe
        class="gmap_iframe h-100 w-100"
        :src="`https://maps.google.com/maps?width=600&amp;height=400&amp;hl=en&amp;q=${contact_page.map_location}&amp;t=&amp;z=12&amp;ie=UTF8&amp;iwloc=B&amp;output=embed&origin=${origin}`"
      ></iframe>
    </div>
  </div>

  <div class="lg-mt-80 mt-100">
    <div class="container">
      <div class="title-three mb-10 text-center">
        <h2>{{ contact_page.title }}</h2>
      </div>
      <div class="row gx-lg-5 justify-content-center">
        <div class="col-lg-4 col-md-6">
          <div class="card-style-eight mt-40 text-center">
            <div
              class="icon rounded-circle d-flex align-items-center justify-content-center m-auto"
            >
              <img :src="sanitizeHtml(contact_page.feature_one_icon)" alt="icon" />
            </div>
            <h5 class="m0 pt-30 pb-5">{{ contact_page.feature_one_title }}</h5>
            <span>{{ contact_page.feature_one_description }}</span>
          </div>
          <!-- /.card-style-eight -->
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card-style-eight mt-40 text-center">
            <div
              class="icon rounded-circle d-flex align-items-center justify-content-center m-auto"
            >
              <img :src="contact_page.feature_two_icon" alt="" />
            </div>
            <h5 class="m0 pt-30 pb-5">{{ contact_page.feature_two_title }}</h5>
            <span>{{ contact_page.feature_two_description }}</span>
          </div>
          <!-- /.card-style-eight -->
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card-style-eight mt-40 text-center">
            <div
              class="icon rounded-circle d-flex align-items-center justify-content-center m-auto"
            >
              <img :src="sanitizeHtml(contact_page.feature_three_icon)" alt="icon" />
            </div>
            <h5 class="m0 pt-30 pb-5">{{ contact_page.feature_three_title }}</h5>
            <span>{{ contact_page.feature_three_description }}</span>
          </div>
          <!-- /.card-style-eight -->
        </div>
      </div>
    </div>
  </div>

  <!--
		=====================================================
			Contact Section One
		=====================================================
		-->
  <div class="contact-section-one theme-bg-dark-two mt-100 lg-mt-80 pt-90 lg-pt-40 pb-100 lg-pb-60">
    <div class="container">
      <div class="row">
        <div class="col-xl-10 m-auto">
          <div class="title-three lg-mb-40 mb-70 text-center">
            <h2>{{ contact_page.form_title }}</h2>
          </div>
        </div>
      </div>

      <div class="form-style-one">
        <form @submit.prevent="submit" id="contact-form" data-toggle="validator">
          <div class="messages"></div>
          <div class="row controls">
            <div class="col-12">
              <div class="input-group-meta form-group mb-40">
                <label for="">{{ trans('Name') }}*</label>
                <input
                  type="text"
                  placeholder="Jhone Doe"
                  v-model="form.name"
                  required="required"
                  data-error="Name is required."
                />
                <div class="help-block with-errors"></div>
              </div>
            </div>
            <div class="col-12">
              <div class="input-group-meta form-group mb-40">
                <label for="">{{ trans('Email') }}*</label>
                <input
                  type="email"
                  placeholder="Email Address*"
                  v-model="form.email"
                  required="required"
                  data-error="Valid email is required."
                />
                <div class="help-block with-errors"></div>
              </div>
            </div>
            <div class="col-12">
              <div class="input-group-meta form-group mb-40">
                <label for="">{{ trans('Subject') }}*</label>
                <input
                  type="text"
                  placeholder="Subject*"
                  v-model="form.subject"
                  required="required"
                  data-error="Valid subject is required."
                />
                <div class="help-block with-errors"></div>
              </div>
            </div>
            <div class="col-12">
              <div class="input-group-meta form-group mb-35">
                <textarea
                  placeholder="Write Message here.."
                  v-model="form.message"
                  required="required"
                  data-error="Please,leave us a message."
                ></textarea>
                <div class="help-block with-errors"></div>
              </div>
            </div>
            <div class="col-12">
              <button class="btn-eight tran3s w-100">
                {{ contact_page.form_button_text ?? 'Send Message' }}
              </button>
            </div>
          </div>
        </form>
      </div>
      <!-- /.form-style-one -->
    </div>
  </div>
  <!-- /.contact-section-one -->
</template>
