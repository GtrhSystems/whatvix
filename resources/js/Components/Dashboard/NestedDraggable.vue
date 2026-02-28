<script setup>
import draggable from 'vuedraggable'
import { menu } from '@/Composables/menuComposable'
import { ref } from 'vue'
defineProps({
  tasks: {
    required: true
  }
})
const dragging = ref(false)
const remove = (id) => {
  menu.contentId = id
  menu.removeItem()
}
const edit = (id) => {
  menu.contentId = id
  menu.editItem()
}
</script>

<style scoped>
.dragArea:first-child {
  padding-left: 0px;
}

.dragArea {
  margin-top: 10px;
  min-height: 10px;
  padding-left: 20px;
  padding-bottom: 10px;
}

.dragArea li {
  list-style: none;
  cursor: move;
}
</style>

<template>
  <draggable class="dragArea" tag="ul" :list="tasks" :group="{ name: 'g' }" item-key="text" @start="dragging = true"
    @end="dragging = false">
    <template #item="{ element }">
      <li>
        <div class="flex items-center justify-between rounded border p-2 dark:border-gray-600">
          <p>
            <Icon class="inline text-lg" icon="tdesign:drag-move"></Icon>
            {{ element.text }}
          </p>
          <div>
            <button class="btn" @click="edit(element.id)">
              <Icon class="text-lg" icon="fe:pencil"></Icon>
            </button>
            <button class="btn" @click="remove(element.id)">
              <Icon class="text-lg" icon="fe:trash"></Icon>
            </button>
          </div>
        </div>
        <nested-draggable :tasks="element.children" />
      </li>
    </template>
  </draggable>
</template>
