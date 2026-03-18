<script setup lang="ts">
    import { computed } from 'vue';

    interface AssignmentCardCourse {
        code: string;
        name: string;
    }

    interface AssignmentCardTheme {
        primary: string;
        outline: string;
        background: string;
        accentBackground: string;
        accentForeground: string;
        foreground: string;
    }

    interface AssignmentCardDetails {
        course: AssignmentCardCourse;
        theme: AssignmentCardTheme;
    }

    interface AssignmentCardProps {
        details: AssignmentCardDetails;
        zoom: string;
        isDragged: boolean;
    }

    defineEmits<{
        dragstart: [event: DragEvent];
        dragend: [event: DragEvent];
    }>();

    const props = defineProps<AssignmentCardProps>();

    const cardPaddingClass = computed(() => {
        return props.zoom === 'small' ? 'px-2 py-1.5' : 'px-3 py-2';
    });

    const badgeClass = computed(() => {
        return props.zoom === 'small' ? 'text-[8px] px-1.5 py-0' : 'text-[10px] px-2 py-0.5';
    });

    const titleClass = computed(() => {
        return props.zoom === 'small' ? 'text-[10px]' : 'mt-2 text-sm';
    });
</script>

<template>
    <div
        class="m-1 flex h-[calc(100%-0.5rem)] flex-col justify-between rounded-xl border shadow-[0_10px_20px_rgba(0,0,0,0.08)]"
        :class="[
            cardPaddingClass,
            isDragged ? 'cursor-grabbing opacity-55' : 'cursor-grab',
        ]"
        :style="{
            backgroundColor: details.theme.background,
            borderColor: details.theme.outline,
        }"
        draggable="true"
        @dragstart="$emit('dragstart', $event)"
        @dragend="$emit('dragend', $event)"
    >
        <div class="flex items-center justify-between gap-1">
            <span
                class="rounded-full font-bold uppercase tracking-wide truncate"
                :class="badgeClass"
                :style="{
                    backgroundColor: details.theme.accentBackground,
                    color: details.theme.accentForeground,
                }"
            >
                {{ details.course.code }}
            </span>
            <span
                v-if="zoom !== 'small'"
                class="h-2.5 w-2.5 shrink-0 rounded-full"
                :style="{ backgroundColor: details.theme.primary }"
            />
        </div>

        <div
            class="leading-tight font-semibold wrap-break-word"
            :class="titleClass"
            :style="{ color: details.theme.foreground }"
        >
            {{ details.course.name }}
        </div>
    </div>
</template>
