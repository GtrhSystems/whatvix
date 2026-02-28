<script setup>
import { computed } from "vue";

const props = defineProps(["meta"]);

const previewQuestion = computed(
    () => props.meta?.name || "Your question will appear here"
);
const previewSelectableCount = computed(() => props.meta?.selectableCount || 4);
const previewOptions = computed(() => props.meta?.values || 4);
</script>

<template>
    <div class="card mx-auto max-w-sm rounded-lg p-3 font-sans shadow-md">
        <p class="mb-1 text-xs font-semibold">{{ previewQuestion }}</p>

        <div class="mb-4 flex items-center dark:text-gray-500">
            <Icon icon="bxs:check-circle" class="mr-0.5 mt-px text-sm" />
            <Icon
                icon="bxs:check-circle"
                class="-ml-2 mt-px text-sm"
                v-if="previewSelectableCount > 1"
            />
            <span class="text-[10px]">{{
                previewSelectableCount > 1 ? "Select one or more" : "Select one"
            }}</span>
        </div>
        <div class="space-y-2">
            <div
                class="flex items-center"
                v-for="(op, i) in previewOptions"
                :key="op"
            >
                <div class="w-5">
                    <Icon
                        :icon="
                            previewOptions.length === i + 1
                                ? 'bxs:check-circle'
                                : 'hugeicons:circle'
                        "
                        class="mr-1"
                        :class="
                            previewOptions.length === i + 1
                                ? ' text-green-600'
                                : 'text-sm'
                        "
                    />
                </div>
                <label for="no" class="text-xs">{{
                    op || "Option " + (i + 1)
                }}</label>
                <span class="ml-auto text-xs text-gray-500">{{ i }}</span>
            </div>
        </div>
        <div class="ml-auto mt-2 h-1 w-[89%] rounded bg-green-600"></div>

        <p class="mt-2 text-right text-[10px] text-gray-500">
            {{ new Date().toLocaleTimeString() }}
        </p>

        <p class="mt-2 text-center text-xs font-semibold text-green-600">
            {{ trans("View votes") }}
        </p>
    </div>
</template>
