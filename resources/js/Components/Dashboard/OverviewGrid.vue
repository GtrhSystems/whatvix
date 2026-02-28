<script setup>
const props = defineProps({
  items: Array,
  grid: {
    type: Number,
    default: 4
  }
})
const gridClass = (grid) => {
  return `lg:grid-cols-${grid}`
}

const randomCardStyles = [
  'bg-teal-500 text-teal-600 bg-opacity-20',
  'bg-green-600 text-green-600 bg-opacity-20',
  'bg-orange-500 text-orange-600 bg-opacity-20',
  'bg-violet-600 text-violet-600 bg-opacity-20',
  'bg-info-500 text-info-600 bg-opacity-20',
  'bg-purple-600 text-purple-600 bg-opacity-20',
  'bg-pink-500 text-pink-600 bg-opacity-20',
  'bg-danger-500 text-danger-600 bg-opacity-20'
]
</script>

<template>
  <section v-if="items.length" :class="gridClass(grid)" class="mb-4 grid grid-cols-1 gap-4">
    <div class="card rounded-2xl" v-for="(item, index) in items" :key="item">
      <div class="flex items-center gap-3 p-5">
        <div
          class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-opacity-10"
          :class="item.style || randomCardStyles[index]"
        >
          <Icon :icon="item.icon" class="text-2xl" />
        </div>
        <div class="flex flex-1 flex-col">
          <p class="text-sm tracking-wide text-slate-500">
            {{ item.title }}
          </p>
          <h5>{{ item.value }}</h5>
        </div>
      </div>
    </div>
  </section>
</template>
