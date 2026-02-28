<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import NavMenu from './NavMenu.vue'
import MiniMenu from './MiniMenu.vue'
import sharedComposable from '@/Composables/sharedComposable'

const { authUser } = sharedComposable()
const primarySettings = usePage().props.primaryData

const isLoggedIn = computed(() => {
  return authUser.value?.id
})

const isHomePage = computed(() => {
  return usePage().url === '/'
})
</script>

<template>
  <header class="theme-main-menu menu-style-three menu-overlay sticky-menu">
    <div class="info-row space-X">
      <div class="d-md-flex justify-content-between">
        <ul class="style-none d-flex justify-content-center up-nav flex-wrap">
          <MiniMenu />
        </ul>
        <ul class="style-none d-none d-md-flex contact-info">
          <li class="d-flex align-items-center">
            <img src="/assets/frontend/images/icon/icon_26.svg" alt="" class="lazy-img icon me-2" />
            <a :href="`tel:${sanitizeHtml(primarySettings.contact_phone)}`" class="fw-500">{{
              primarySettings.contact_phone
            }}</a>
          </li>
          <li class="d-flex align-items-center">
            <img src="/assets/frontend/images/icon/icon_27.svg" alt="" class="lazy-img icon me-2" />
            <a :href="`mailto:${sanitizeHtml(primarySettings.contact_email)}`" class="fw-500">
              {{ primarySettings.contact_email }}
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="inner-content space-X">
      <div class="top-header position-relative">
        <div class="d-flex align-items-center justify-content-between">
          <div class="logo order-lg-0">
            <Link href="/" class="d-flex align-items-center">
              <img
                :src="primarySettings?.deep_logo ?? '/assets/frontend/images/logo/logo_03.svg'"
                alt=""
              />
            </Link>
          </div>
          <!-- logo -->
          <div class="right-widget ms-lg-0 me-lg-0 order-lg-3 me-3 ms-auto">
            <ul class="d-flex align-items-center style-none">
              <template v-if="isLoggedIn">
                <li class="d-none d-md-inline-block ms-lg-5 ms-3">
                  <a href="/user/dashboard" class="signup-btn-one icon-link version-three">
                    <span>{{ trans('Dashboard') }}</span>
                  </a>
                </li>
              </template>
              <template v-else>
                <template v-if="isHomePage">
                  <li class="d-flex align-items-center">
                    <img src="/assets/frontend/images/icon/icon_28.svg" alt="" class="icon me-2" />
                    <a href="/login" class="fw-500">{{ trans('Login') }}</a>
                  </li>
                  <li class="d-none d-md-inline-block ms-lg-4 ms-3">
                    <a href="/register" class="signup-btn-three icon-link">
                      <span>{{ trans('Sign Up') }}</span>
                    </a>
                  </li>
                </template>

                <template v-else>
                  <li class="d-flex align-items-center me-lg-4 me-3">
                    <img src="/assets/frontend/images/icon/icon_28.svg" alt="" class="icon me-2" />
                    <a href="/login" class="fw-500">{{ trans('Login') }}</a>
                  </li>
                  <li class="d-none d-md-block">
                    <a href="/register" class="fw-500 signup-btn-one version-three">{{
                      trans('Signup')
                    }}</a>
                  </li>
                </template>
              </template>
            </ul>
          </div>
          <NavMenu />
        </div>
      </div>
      <!--/.top-header-->
    </div>
    <!-- /.inner-content -->
  </header>
</template>
