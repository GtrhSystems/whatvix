<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue'
import ValidationErrors from '@/Components/Dashboard/ValidationErrors.vue'

defineOptions({ layout: AdminLayout })
const props = defineProps({
  permissions_groups: Array,
  role: Object
})

const form = useForm({
  permissions: [],
  name: '',
  _method: 'PUT'
})

const selectedGroups = ref([])

onMounted(() => {
  form.permissions = props.role.permissions.map((p) => p.name)
  form.name = props.role.name

  props.permissions_groups.forEach((group) => {
    if (isGroupFullySelected(group)) {
      selectedGroups.value.push(group.group_name)
    }
  })
})

const isAllSelected = computed(() =>
  props.permissions_groups.every((group) => selectedGroups.value.includes(group.group_name))
)

const totalPermissions = computed(() =>
  props.permissions_groups.reduce((total, group) => total + group.permissions.length, 0)
)

const isGroupFullySelected = (group) => {
  const groupPermissions = group.permissions.map((p) => p.name)
  return groupPermissions.length > 0 && groupPermissions.every((p) => form.permissions.includes(p))
}

const togglePermission = (permissionName) => {
  const index = form.permissions.indexOf(permissionName)
  index === -1 ? form.permissions.push(permissionName) : form.permissions.splice(index, 1)
}

const updateGroupSelection = (groupName) => {
  const group = props.permissions_groups.find((g) => g.group_name === groupName)
  const groupPermissions = group.permissions.map((p) => p.name)
  const isSelected = selectedGroups.value.includes(groupName)

  if (isSelected) {
    selectedGroups.value = selectedGroups.value.filter((g) => g !== groupName)
    form.permissions = form.permissions.filter((p) => !groupPermissions.includes(p))
  } else {
    selectedGroups.value.push(groupName)
    groupPermissions.forEach((p) => {
      if (!form.permissions.includes(p)) form.permissions.push(p)
    })
  }
}

// Event handlers
const toggleAllPermissions = () => {
  if (form.permissions.length === totalPermissions.value) {
    form.permissions = []
    selectedGroups.value = []
  } else {
    form.permissions = props.permissions_groups.flatMap((group) =>
      group.permissions.map((p) => p.name)
    )
    selectedGroups.value = props.permissions_groups.map((g) => g.group_name)
  }
}

const handlePermissionChange = (permissionName) => {
  togglePermission(permissionName)

  props.permissions_groups.forEach((group) => {
    const groupPermissions = group.permissions.map((p) => p.name)
    if (groupPermissions.includes(permissionName)) {
      const isFullySelected = groupPermissions.every((p) => form.permissions.includes(p))
      const groupIndex = selectedGroups.value.indexOf(group.group_name)

      if (isFullySelected && groupIndex === -1) {
        selectedGroups.value.push(group.group_name)
      } else if (!isFullySelected && groupIndex !== -1) {
        selectedGroups.value.splice(groupIndex, 1)
      }
    }
  })
}

const updateRole = () => {
  form.post(route('admin.role.update', props.role.id))
}
</script>

<template>
  <div class="space-y-6">
    <div class="card">
      <div class="card-body">
        <div class="mb-3">
          <label class="label label-md">{{ trans('Role Name') }}</label>
          <input
            v-model="form.name"
            type="text"
            class="input"
            :placeholder="trans('Enter role name')"
            required
          />
        </div>

        <ValidationErrors />

        <label class="toggle toggle-sm">
          <input
            :checked="isAllSelected"
            @change="toggleAllPermissions"
            class="toggle-input peer sr-only"
            type="checkbox"
          />
          <div class="toggle-body"></div>
          <span class="label label-md">{{ trans('Check All Permissions') }}</span>
        </label>

        <hr class="my-4" />

        <div
          v-for="group in permissions_groups"
          :key="group.group_name"
          class="mb-5 grid grid-cols-12 gap-4"
        >
          <div class="col-span-3">
            <label class="flex cursor-pointer items-center gap-2">
              <input
                :checked="selectedGroups.includes(group.group_name)"
                @change="updateGroupSelection(group.group_name)"
                type="checkbox"
                class="checkbox"
              />
              <span class="label">{{ group.group_name }}</span>
            </label>
          </div>

          <div class="col-span-9 flex flex-col gap-1">
            <label
              v-for="permission in group.permissions"
              :key="permission.id"
              class="flex cursor-pointer items-center gap-2"
            >
              <input
                :checked="form.permissions.includes(permission.name)"
                @change="handlePermissionChange(permission.name)"
                type="checkbox"
                class="checkbox"
              />
              <span class="label">{{ permission.name }}</span>
            </label>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <button @click="updateRole" class="btn btn-primary" :disabled="form.processing">
          <Icon icon="bx:save" />
          {{ trans('Update') }}
        </button>
      </div>
    </div>
  </div>
</template>
