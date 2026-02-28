<script setup>
import { onMounted, onUpdated } from 'vue'

import ActionModal from '@/Components/Dashboard/ActionModal.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import ValidationErrors from '@/Components/Dashboard/ValidationErrors.vue'
import Header from '@/Layouts/Admin/Header.vue'
import Sidebar from '@/Layouts/Admin/Sidebar.vue'
import dropdown from '@/Plugins/Admin/dropdown'
import AssetModal from '@/Components/Dashboard/AssetModal.vue'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'
import ToastrContainer from '@/Components/ToastrContainer.vue'

onMounted(function () {
  dropdown.init()
})

onUpdated(function () {
  dropdown.init()
})
</script>

<template>
  <div>
    <ToastrContainer />
    <Modal />
    <ActionModal />
    <AssetModal />
    <Sidebar />
    <div class="wrapper">
      <Header />
      <ValidationErrors />
      <div class="content">
        <main class="container flex-grow p-4 sm:p-6">
          <PageHeader />
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
