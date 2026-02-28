<script setup>
import footerComposable from '@/Composables/footerComposable'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { sanitize } from 'isomorphic-dompurify'
import LanguageSwitch from '@/Components/Web/LanguageSwitch.vue'
import MiniMenu from './MiniMenu.vue'
const primarySettings = usePage().props.primaryData
const { footerLeft, footerRight } = footerComposable()
const form = useForm({
  email: ''
})

const subscribe = () => {
  form.post(route('newsletter.subscribe'))
}
const isExternalAuth = (href) => {
  return ['/login', '/register'].some((path) => href.endsWith(path))
}
</script>
<template>
  <div class="footer-one">
    <div class="container">
      <div class="position-relative">
        <div class="row">
          <div class="col-lg-5 me-auto">
            <div class="footer-intro me-lg-5 pe-xxl-4 mb-40">
              <h3>{{ primarySettings.footer_text }}</h3>
              <form @submit.prevent="subscribe" class="position-relative">
                <input
                  type="email"
                  v-model="form.email"
                  :placeholder="trans('Your email address')"
                />
                <button class="tran3s" type="submit">
                  <img src="/assets/frontend/images/icon/icon_13.svg" alt="" class="me-auto" />
                </button>
              </form>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-6">
            <div class="footer-nav mb-30">
              <ul v-if="footerLeft" class="footer-nav-link style-none">
                <li v-for="item in footerLeft.data" :key="item.id">
                  <component
                    :is="isExternalAuth(item.href) || item.target == '_top' ? 'a' : 'Link'"
                    :href="sanitizeUrl(item.href)"
                    >{{ item.text }}</component
                  >
                </li>
              </ul>
            </div>
          </div>
          <div v-if="footerRight" class="col-lg-2 col-md-4 col-6">
            <div class="footer-nav mb-30">
              <ul class="footer-nav-link style-none">
                <li v-for="item in footerRight.data" :key="item.id">
                  <component
                    :is="isExternalAuth(item.href) || item.target == '_top' ? 'a' : 'Link'"
                    :href="sanitizeUrl(item.href)"
                  >
                    {{ item.text }}
                  </component>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-lg-3 col-md-4">
            <div class="footer-contact mb-30 ps-xxl-5">
              <p class="fw-500">{{ primarySettings.contact_address ?? 'Address' }}</p>
              <a
                :href="`tel:${sanitizeHtml(primarySettings.contact_phone)}`"
                class="tel fw-bold tran3s"
                >{{ primarySettings.contact_phone }}</a
              >
              <ul class="style-none d-flex align-items-center social-icon mt-20">
                <li v-for="(link, platform) in primarySettings.socials" :key="platform">
                  <a :href="sanitizeUrl(link)"
                    ><i class="fa-brands" :class="`fa-${platform}`"></i
                  ></a>
                </li>
              </ul>
              <template v-if="Object.keys($page.props?.languages ?? {}).length > 1">
                <LanguageSwitch classes="mt-5 mb-10" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="bottom-footer">
        <div class="d-md-flex align-items-center justify-content-between">
          <div class="d-flex justify-content-center align-items-center sm-mb-20">
            <ul class="style-none d-flex justify-content-center">
              <MiniMenu />
            </ul>
          </div>
          <p
            class="copyright-text m0 text-center"
            v-html="sanitize(primarySettings.copyright_text)"
          ></p>
        </div>
      </div>
    </div>
  </div>
  <!-- /.footer-one -->
</template>