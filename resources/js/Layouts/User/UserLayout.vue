<script setup>
import { onMounted, onUpdated } from 'vue'

import ActionModal from '@/Components/Dashboard/ActionModal.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import ValidationErrors from '@/Components/Dashboard/ValidationErrors.vue'
import ToastrContainer from '@/Components/ToastrContainer.vue'
import Header from '@/Layouts/Admin/Header.vue'
import Sidebar from '@/Layouts/Admin/Sidebar.vue'
import dropdown from '@/Plugins/Admin/dropdown'
import FloatingDropdown from '@/Components/Dashboard/FloatingDropdown.vue'
import AssetModal from '@/Components/Dashboard/AssetModal.vue'
import PageHeader from '@/Layouts/Admin/PageHeader.vue'

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
      <Header panel="user">
        <ValidationErrors />
        <template #header-options>
          <FloatingDropdown btn-type="slot" btnClass="p-2 flex items-center gap-x-1">
            <template #btnContent>
              <Icon icon="material-symbols:category-outline-rounded" class="text-xl" />
              <span
                v-if="$page.props.activeWorkspace?.id"
                class="whitespace-nowrap text-xs md:text-sm"
              >
                {{ $page.props.activeWorkspace?.name }}
              </span>
              <Icon v-else class="text-xl" icon="carbon:workspace-import" title="Workspaces" />
            </template>
            <ul class="dropdown-list">
              <li
                v-for="workspace in $page.props.userWorkspaces"
                :key="workspace.id"
                class="dropdown-list-item"
              >
                <Link
                  v-if="workspace"
                  as="button"
                  :href="route('user.set-workspace', workspace.id)"
                  method="patch"
                  class="dropdown-btn transition duration-300 ease-in-out dark:hover:bg-primary-600"
                  :class="{
                    'bg-primary-600 text-white dark:bg-primary-500 dark:text-white':
                      $page.props.activeWorkspace?.id === workspace.id
                  }"
                >
                  <span class="truncate whitespace-nowrap">
                    {{ workspace.name }}
                  </span>
                </Link>
              </li>
              <li class="dropdown-list-item">
                <component
                  :is="$page.props?.activeModule === null ? 'Link' : 'a'"
                  :href="route('user.workspaces.create')"
                  class="dropdown-btn"
                >
                  <Icon icon="bx:plus" class="text-xl text-slate-400" />
                  <span>{{ trans('New Workspaces') }}</span>
                </component>
              </li>
            </ul>
          </FloatingDropdown>
        </template>
      </Header>
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
