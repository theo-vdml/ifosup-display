<script setup lang="ts">
    import { computed, nextTick, onMounted, ref, watch } from 'vue';
    import { DoorOpen, Maximize2, Minimize2, Minus, Plus } from 'lucide-vue-next';
    import PlaceholderPattern from './PlaceholderPattern.vue';
    import { DerivedTheme, useThemeDerivation } from '@/composables/useThemeDerivation';
    import { useAppearance } from '@/composables/useAppearance';
    import { useSyncScroll } from '@/composables/useSyncScroll';
    import useSchedulerView from '@/composables/useSchedulerView';
    import SchedulerDragPreview from './SchedulerDragPreview.vue';

    type AssignmentWithRelations = Assignment & {
        course: Course;
        room?: Room;
    };

    interface SchedulerProps {
        fromDate?: string
        toDate?: string
        rooms: Room[]
        assignments: AssignmentWithRelations[]
    };

    const props = withDefaults(defineProps<SchedulerProps>(), {
        fromDate: '2026-03-01',
        toDate: '2026-03-30',
    });

    type CellDetails = {
        course: Course,
        theme: DerivedTheme;
    };

    const periods: Array<{ key: AssignmentPeriod; label: string }> = [
        { key: 'morning', label: 'Matin' },
        { key: 'afternoon', label: 'Apres-midi' },
        { key: 'evening', label: 'Soir' },
    ];

    const toUtcDate = (value: string) => new Date(`${value}T00:00:00Z`);

    const buildDateKeys = (startDate: string, endDate: string) => {
        const start = toUtcDate(startDate);
        const end = toUtcDate(endDate);
        const list: string[] = [];

        for (
            const cursor = new Date(start);
            cursor.getTime() <= end.getTime();
            cursor.setUTCDate(cursor.getUTCDate() + 1)
        ) {
            list.push(cursor.toISOString().slice(0, 10));
        }

        return list;
    };

    const dateKeys = buildDateKeys(props.fromDate, props.toDate);

    const assignments = ref([...props.assignments]);

    const dateLabelFormatter = new Intl.DateTimeFormat('fr-FR', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        timeZone: 'UTC',
    });

    const dates = computed(() => {
        return dateKeys.map((iso) => {
            const cursor = toUtcDate(iso);

            return {
                key: iso,
                label: dateLabelFormatter.format(cursor),
            };
        });
    });

    const { resolvedAppearance } = useAppearance();
    const isDarkMode = computed(() => resolvedAppearance.value === 'dark');

    const { getThemeFromSeed } = useThemeDerivation(isDarkMode);

    const buildCellKey = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return `${roomId}|${dateKey}|${period}`;
    };

    const cellDetailsMap = computed(() => {
        const map = new Map<string, CellDetails>();

        for (const assigment of assignments.value) {

            if (!assigment.room_id) {
                continue;
            }

            const key = buildCellKey(assigment.room_id, assigment.date, assigment.period);

            map.set(key, {
                course: assigment.course,
                theme: getThemeFromSeed(assigment.course.name)
            });
        }

        return map;
    });

    const getCellDetails = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        const key = buildCellKey(roomId, dateKey, period);
        return cellDetailsMap.value.get(key);
    };

    const draggedAssignmentKey = ref<string | null>(null);
    const dropTargetKey = ref<string | null>(null);
    const draggedCellDetails = ref<CellDetails | null>(null);
    const dragPreviewRef = ref<HTMLElement | null>(null);

    const isCellOccupied = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return cellDetailsMap.value.has(buildCellKey(roomId, dateKey, period));
    };

    const findAssignmentIndex = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return assignments.value.findIndex((assigment) => {
            return assigment.room_id === roomId
                && assigment.date === dateKey
                && assigment.period === period;
        })
    };

    const updateAssignmentSlot = (
        assignment: Assignment,
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
    ) => {
        assignment.room_id = roomId;
        assignment.date = dateKey;
        assignment.period = period;
    };

    const clearDragState = () => {
        draggedAssignmentKey.value = null;
        dropTargetKey.value = null;
        draggedCellDetails.value = null;
    };

    const BASE_CELL_WIDTH = 288;
    const BASE_CELL_HEIGHT = 96;

    // roomColWidth is intentionally fixed — it only needs to fit room labels
    const roomColWidth = '176px';
    const cellWidth = computed(() => `${Math.round(BASE_CELL_WIDTH * zoomRatio.value)}px`);
    // height range is clamped to 0.75→1.5 to avoid extremes at small/xl zoom
    const cellHeightRatio = computed(() => 0.75 + (zoomRatio.value - 0.5) * 0.5);
    const cellHeight = computed(() => `${Math.round(BASE_CELL_HEIGHT * cellHeightRatio.value)}px`);
    const dateGroupWidth = computed(() => `${Math.round(BASE_CELL_WIDTH * zoomRatio.value) * periods.length}px`);

    const dragPreviewWidth = computed(() => {
        return Math.min(Math.round(BASE_CELL_WIDTH * zoomRatio.value) - 8, 280);
    });

    const onAssignmentDragStart = async (
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
        event: DragEvent,
    ) => {
        const key = buildCellKey(roomId, dateKey, period);
        const details = getCellDetails(roomId, dateKey, period);

        if (!details) {
            return;
        }

        draggedAssignmentKey.value = key;
        dropTargetKey.value = key;
        draggedCellDetails.value = details;

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', key);
            await nextTick();

            if (dragPreviewRef.value) {
                event.dataTransfer.setDragImage(dragPreviewRef.value, 24, 20);
            }
        }
    };

    const onCellDragOver = (
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
        event: DragEvent,
    ) => {
        if (!draggedAssignmentKey.value) {
            return;
        }

        const sourceKey = draggedAssignmentKey.value;
        const targetKey = buildCellKey(roomId, dateKey, period);
        const isSameCell = sourceKey === targetKey;
        const targetUsed = isCellOccupied(roomId, dateKey, period);
        const canDrop = isSameCell || !targetUsed;

        if (!canDrop) {
            dropTargetKey.value = null;

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'none';
            }

            return;
        }

        event.preventDefault();
        dropTargetKey.value = buildCellKey(roomId, dateKey, period);

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }
    };

    const onCellDrop = (
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
        event: DragEvent,
    ) => {
        event.preventDefault();

        const sourceKey = draggedAssignmentKey.value;
        const targetKey = buildCellKey(roomId, dateKey, period);
        const isSameCell = sourceKey === targetKey;
        const targetUsed = isCellOccupied(roomId, dateKey, period);

        if (!sourceKey || isSameCell) {
            clearDragState();
            return;
        }

        if (targetUsed) {
            clearDragState();
            return;
        }

        const [sourceRoomId, sourceDate, sourcePeriod] = sourceKey.split('|');
        const sourceIndex = findAssignmentIndex(
            Number.parseInt(sourceRoomId, 10),
            sourceDate,
            sourcePeriod as AssignmentPeriod,
        );

        if (sourceIndex === -1) {
            clearDragState();
            return;
        }

        const sourceAssignment = assignments.value[sourceIndex];
        updateAssignmentSlot(sourceAssignment, roomId, dateKey, period);

        assignments.value = [...assignments.value];
        clearDragState();
    };

    const isDropTarget = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return dropTargetKey.value === buildCellKey(roomId, dateKey, period);
    };

    const isDraggedAssignment = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return draggedAssignmentKey.value === buildCellKey(roomId, dateKey, period);
    };

    const {
        headerTrackRef,
        sidebarTrackRef,
        gridScrollerRef,
        onGridScroll,
        forceSync
    } = useSyncScroll();

    const {
        zoom,
        zoomRatio,
        isFullscreen,
        canZoomIn,
        canZoomOut,
        zoomIn,
        zoomOut,
        toggleFullscreen,
        zoomLabel,
    } = useSchedulerView();

    onMounted(() => {
        forceSync();
    });

    watch(zoom, () => {
        forceSync();
    });
</script>

<template>
    <div :class="[
        'overflow-hidden border border-zinc-300/60 bg-linear-to-br from-zinc-50 via-white to-zinc-100 dark:border-zinc-700/60 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
        isFullscreen
            ? 'fixed inset-0 z-50 rounded-none'
            : 'relative h-[calc(100vh-8rem)] min-h-112 max-h-[calc(100vh-8rem)] rounded-xl w-full',
    ]">
        <div class="flex h-full min-h-0 flex-col">
            <div class="shrink-0 border-b border-zinc-300/70 dark:border-zinc-700/70">
                <div class="flex">
                    <div class="h-22 shrink-0 bg-zinc-100/80 px-4 align-middle text-xs font-semibold tracking-wide text-zinc-500 uppercase border-r border-zinc-200/60 dark:border-zinc-700/60 dark:bg-zinc-900/70 dark:text-zinc-300 flex items-center justify-center gap-2"
                        :style="{ width: roomColWidth }">
                        <DoorOpen class="h-4 w-4" />
                        Locaux
                    </div>

                    <div class="relative flex-1 overflow-hidden">
                        <div ref="headerTrackRef" class="min-w-max transform-gpu will-change-transform">
                            <div class="flex">
                                <div v-for="date in dates" :key="`date-${date.key}`"
                                    class="h-12 border-r border-b border-zinc-200/70 bg-zinc-100/70 px-3 text-center text-xs font-semibold tracking-wide text-zinc-500 uppercase dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:text-zinc-300 flex items-center justify-center"
                                    :style="{ width: dateGroupWidth }">
                                    {{ date.label }}
                                </div>
                            </div>

                            <div class="flex">
                                <template v-for="date in dates" :key="`periods-${date.key}`">
                                    <div v-for="period in periods" :key="`${date.key}-${period.key}`"
                                        class="h-10 border-r border-zinc-200/60 bg-zinc-100/55 px-3 text-center text-[11px] font-semibold tracking-wide text-zinc-400 dark:border-zinc-700/70 dark:bg-zinc-900/45 dark:text-zinc-400 flex items-center justify-center"
                                        :style="{ width: cellWidth }">
                                        {{ period.label }}
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex min-h-0 flex-1">
                <div class="shrink-0 overflow-hidden border-r border-zinc-300/70 dark:border-zinc-700/70"
                    :style="{ width: roomColWidth }">
                    <div ref="sidebarTrackRef" class="transform-gpu will-change-transform">
                        <div v-for="room in rooms" :key="`room-${room.id}`"
                            class="border-b border-zinc-200/70 bg-zinc-100/60 px-4 py-2.5 text-left font-medium text-zinc-500 dark:border-zinc-700/70 dark:bg-zinc-900/55 dark:text-zinc-300 flex items-center"
                            :style="{ height: cellHeight }">
                            {{ room.name }}
                        </div>
                    </div>
                </div>

                <div ref="gridScrollerRef" class="min-h-0 min-w-0 flex-1 overflow-auto" @scroll="onGridScroll">
                    <div class="min-w-max">
                        <div v-for="room in rooms" :key="`row-${room.id}`" class="flex">
                            <template v-for="date in dates" :key="`${room.id}-${date.key}`">
                                <div v-for="period in periods" :key="`${room.id}-${date.key}-${period.key}`"
                                    class="group relative border-r border-b border-zinc-100 bg-white px-0 py-0 transition-colors dark:border-zinc-800 dark:bg-zinc-950"
                                    :class="isDropTarget(room.id, date.key, period.key)
                                        ? 'bg-zinc-100/80 dark:bg-zinc-900/80'
                                        : ''" :style="{ width: cellWidth, height: cellHeight }"
                                    @dragover="onCellDragOver(room.id, date.key, period.key, $event)"
                                    @drop="onCellDrop(room.id, date.key, period.key, $event)">
                                    <div v-if="getCellDetails(room.id, date.key, period.key)"
                                        class="m-1 flex h-[calc(100%-0.5rem)] flex-col justify-between rounded-xl border shadow-[0_10px_20px_rgba(0,0,0,0.08)]"
                                        :class="[
                                            zoom === 'small' ? 'px-2 py-1.5' : 'px-3 py-2',
                                            isDraggedAssignment(room.id, date.key, period.key)
                                                ? 'cursor-grabbing opacity-55'
                                                : 'cursor-grab',
                                        ]" :style="{
                                            backgroundColor: getCellDetails(room.id, date.key, period.key)?.theme.background,
                                            borderColor: getCellDetails(room.id, date.key, period.key)?.theme.outline,
                                        }" draggable="true"
                                        @dragstart="onAssignmentDragStart(room.id, date.key, period.key, $event)"
                                        @dragend="clearDragState()">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="rounded-full font-bold uppercase tracking-wide truncate"
                                                :class="zoom === 'small' ? 'text-[8px] px-1.5 py-0' : 'text-[10px] px-2 py-0.5'"
                                                :style="{
                                                    backgroundColor: getCellDetails(room.id, date.key, period.key)?.theme.accentBackground,
                                                    color: getCellDetails(room.id, date.key, period.key)?.theme.accentForeground,
                                                }">
                                                {{ getCellDetails(room.id, date.key, period.key)?.course.code }}
                                            </span>
                                            <span v-if="zoom !== 'small'" class="h-2.5 w-2.5 shrink-0 rounded-full"
                                                :style="{
                                                    backgroundColor: getCellDetails(room.id, date.key, period.key)?.theme.primary,
                                                }" />
                                        </div>

                                        <div class="leading-tight font-semibold wrap-break-word"
                                            :class="zoom === 'small' ? 'text-[10px]' : 'mt-2 text-sm'" :style="{
                                                color: getCellDetails(room.id, date.key, period.key)?.theme.foreground
                                            }">
                                            {{ getCellDetails(room.id, date.key, period.key)?.course.name }}
                                        </div>
                                    </div>

                                    <div v-else class="flex h-full items-center justify-center relative">
                                        <PlaceholderPattern v-if="!isDropTarget(room.id, date.key, period.key)" />
                                        <Plus
                                            class="h-5 w-5 text-zinc-400 opacity-0 transition-opacity group-hover:opacity-100 dark:text-zinc-600" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-3 right-3 z-10 flex items-center gap-1.5">
            <div
                class="flex items-center overflow-hidden rounded-lg border border-zinc-200/80 bg-white/80 shadow-sm backdrop-blur-sm dark:border-zinc-700/80 dark:bg-zinc-900/80">
                <button
                    class="flex cursor-pointer items-center justify-center px-2 py-1.5 text-zinc-400 transition-colors hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-30 dark:text-zinc-500 dark:hover:text-zinc-300"
                    :disabled="!canZoomOut" @click="zoomOut">
                    <Minus class="h-3.5 w-3.5" />
                </button>
                <span
                    class="border-x border-zinc-200/80 px-2 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-400 dark:border-zinc-700/80 dark:text-zinc-500">
                    {{ zoomLabel }}
                </span>
                <button
                    class="flex cursor-pointer items-center justify-center px-2 py-1.5 text-zinc-400 transition-colors hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-30 dark:text-zinc-500 dark:hover:text-zinc-300"
                    :disabled="!canZoomIn" @click="zoomIn">
                    <Plus class="h-3.5 w-3.5" />
                </button>
            </div>

            <button
                class="flex cursor-pointer items-center justify-center rounded-lg border border-zinc-200/80 bg-white/80 p-1.5 text-zinc-400 shadow-sm backdrop-blur-sm transition-colors hover:border-zinc-300 hover:text-zinc-600 dark:border-zinc-700/80 dark:bg-zinc-900/80 dark:text-zinc-500 dark:hover:border-zinc-600 dark:hover:text-zinc-300"
                @click="toggleFullscreen">
                <Maximize2 v-if="!isFullscreen" class="h-3.5 w-3.5" />
                <Minimize2 v-else class="h-3.5 w-3.5" />
            </button>
        </div>

        <Teleport to="body">
            <div v-if="draggedCellDetails" ref="dragPreviewRef" class="fixed pointer-events-none opacity-95 -rotate-2"
                :style="{ top: '-9999px', left: '-9999px', zIndex: 9999 }">
                <SchedulerDragPreview :details="draggedCellDetails" :zoom="zoom" :width="dragPreviewWidth" />
            </div>
        </Teleport>
    </div>
</template>
