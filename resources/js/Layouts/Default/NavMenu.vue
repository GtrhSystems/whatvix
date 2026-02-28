<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import SubMenu from '@/Layouts/Default/SubMenu.vue'
import MiniMenu from './MiniMenu.vue'
import { ref } from 'vue'
const mainMenu = usePage().props.menus.filter((item) => item.position === 'main-menu')[0]
const primarySettings = usePage().props.primaryData

const mobileMenuState = ref(false)
const isExternalAuth = (href) => {
  return ['/login', '/register'].some((path) => href.endsWith(path))
}
</script>

<template>
  <nav class="navbar navbar-expand-lg p0 order-lg-2 ms-lg-4" v-if="mainMenu">
    <button
      class="navbar-toggler d-block d-lg-none"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarNav"
      aria-controls="navbarNav"
      aria-expanded="false"
      aria-label="Toggle navigation"
      @click="mobileMenuState = !mobileMenuState"
    >
      <span></span>
    </button>
    <div
      v-if="mobileMenuState"
      class="mobile-menu-overlay d-block d-lg-none"
      @click="mobileMenuState = !mobileMenuState"
      data-bs-toggle="collapse"
      data-bs-target="#navbarNav"
    ></div>
    <div class="navbar-collapse collapse" id="navbarNav">
      <ul class="navbar-nav align-items-lg-center">
        <li class="d-block d-lg-none">
          <div class="logo">
            <Link href="/" class="d-block"
              ><img
                :src="primarySettings?.deep_logo ?? '/assets/frontend/images/logo/logo_03.svg'"
                alt=""
            /></Link>
          </div>
        </li>

        <li
          v-for="item in mainMenu.data ?? []"
          :key="item.id"
          class="nav-item"
          :class="{ dropdown: item.children?.length > 0 }"
        >
          <SubMenu v-if="item.children?.length > 0" :item="item" />
          <component
            :is="isExternalAuth(item.href) || item.target == '_top' ? 'a' : 'Link'"
            class="nav-link"
            v-else
            :href="sanitizeUrl(item.href)"
            :target="item.target"
            role="button"
          >
            {{ item.text }}
          </component>
        </li>

        <li class="d-md-none mt-40 pe-3 ps-3">
          <div class="info-row d-block">
            <div>
              <ul class="style-none contact-info pb-25">
                <li class="d-flex align-items-center mb-10">
                  <img
                    src="/assets/frontend/images/icon/icon_26.svg"
                    alt=""
                    class="lazy-img icon me-2"
                  />
                  <a :href="`tel:${sanitizeHtml(primarySettings.contact_phone)}`" class="fw-500">{{
                    primarySettings.contact_phone
                  }}</a>
                </li>
                <li class="d-flex align-items-center">
                  <img
                    src="/assets/frontend/images/icon/icon_27.svg"
                    alt=""
                    class="lazy-img icon me-2"
                  />
                  <a
                    :href="`mailto:${sanitizeHtml(primarySettings.contact_email)}`"
                    class="fw-500"
                    >{{ primarySettings.contact_email }}</a
                  >
                </li>
              </ul>
              <ul class="style-none d-flex justify-content-center up-nav flex-wrap">
                <MiniMenu />
              </ul>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </nav>
</template>