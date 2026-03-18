<script setup lang="ts">
    import { router } from '@inertiajs/vue3';
    import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
    import { BookOpen, DoorOpen, Maximize2, Minimize2, Minus, Plus } from 'lucide-vue-next';
    import PlaceholderPattern from './PlaceholderPattern.vue';
    import { DerivedTheme, useThemeDerivation } from '@/composables/useThemeDerivation';
    import { useAppearance } from '@/composables/useAppearance';
    import { useSyncScroll } from '@/composables/useSyncScroll';
    import useSchedulerView, { type ZoomLevel } from '@/composables/useSchedulerView';
    import { schedule } from '@/routes';
    import SchedulerDateRangePicker from './SchedulerDateRangePicker.vue';
    import SchedulerAssignmentCard from './SchedulerAssignmentCard.vue';
    import SchedulerDragPreview from './SchedulerDragPreview.vue';

    interface SchedulerProps {
        fromDate?: string
        toDate?: string
        rooms: Room[]
        courses: Course[]
        assignments: AssignmentWithRelations[]
    }

    type CellDetails = {
        course: Course,
        theme: DerivedTheme;
    }

    type PersistedAssignment = AssignmentWithRelations & {
        id: number;
    }

    const props = withDefaults(defineProps<SchedulerProps>(), {
        fromDate: '2026-03-01',
        toDate: '2026-03-30',
    });

    const SCHEDULER_ZOOM_COOKIE = 'scheduler_zoom';
    const SCHEDULER_DRAWER_COOKIE = 'scheduler_drawer_open';
    const COOKIE_MAX_AGE_SECONDS = 60 * 60 * 24 * 30;

    const readCookie = (name: string) => {
        const prefix = `${name}=`;
        const cookie = document.cookie
            .split('; ')
            .find((entry) => entry.startsWith(prefix));

        if (!cookie) {
            return null;
        }

        return decodeURIComponent(cookie.slice(prefix.length));
    };

    const writeCookie = (name: string, value: string) => {
        document.cookie = `${name}=${encodeURIComponent(value)}; Max-Age=${COOKIE_MAX_AGE_SECONDS}; Path=/; SameSite=Lax`;
    };

    const parseInitialZoom = (): ZoomLevel => {
        const value = readCookie(SCHEDULER_ZOOM_COOKIE);

        if (value === '0.5') {
            return 'small';
        }

        if (value === '1.5') {
            return 'large';
        }

        if (value === '2') {
            return 'xl';
        }

        return 'normal';
    };

    const parseInitialDrawerState = () => {
        return readCookie(SCHEDULER_DRAWER_COOKIE) === '1';
    };

    const zoomToCookieValue = (value: ZoomLevel) => {
        if (value === 'small') {
            return '0.5';
        }

        if (value === 'large') {
            return '1.5';
        }

        if (value === 'xl') {
            return '2';
        }

        return '1';
    };

    const initialZoom = parseInitialZoom();
    const initialCoursePanelOpen = parseInitialDrawerState();

    const formatDate = (date: Date) => {
        return date.toISOString().slice(0, 10);
    };

    const now = new Date();
    const defaultFromDate = formatDate(new Date(now.getTime() - 24 * 60 * 60 * 1000));
    const defaultToDate = formatDate(new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000));

    const toUtcDate = (value: string) => new Date(`${value}T00:00:00Z`);

    const fromDateInput = ref(props.fromDate ?? defaultFromDate);
    const toDateInput = ref(props.toDate ?? defaultToDate);
    const isRangeLoading = ref(false);
    let rangeReloadTimer: number | null = null;

    const periods: Array<{ key: AssignmentPeriod; label: string }> = [
        { key: 'morning', label: 'Matin' },
        { key: 'afternoon', label: 'Apres-midi' },
        { key: 'evening', label: 'Soir' },
    ];

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

    const dateKeys = computed(() => buildDateKeys(props.fromDate, props.toDate));

    const assignments = ref<PersistedAssignment[]>([...props.assignments as PersistedAssignment[]]);
    const courses = computed(() => {
        return [...props.courses].sort((a, b) => a.code.localeCompare(b.code, 'fr'));
    });

    const dateLabelFormatter = new Intl.DateTimeFormat('fr-FR', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        timeZone: 'UTC',
    });

    const dates = computed(() => {
        return dateKeys.value.map((iso) => {
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

        for (const assignment of assignments.value) {
            if (!assignment.room_id) {
                continue;
            }

            const key = buildCellKey(assignment.room_id, assignment.date, assignment.period);

            map.set(key, {
                course: assignment.course,
                theme: getThemeFromSeed(assignment.course.name),
            });
        }

        return map;
    });

    const getCellDetails = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        const key = buildCellKey(roomId, dateKey, period);
        return cellDetailsMap.value.get(key);
    };

    const getCourseDetails = (course: Course): CellDetails => {
        return {
            course,
            theme: getThemeFromSeed(course.name),
        };
    };

    const draggedAssignmentKey = ref<string | null>(null);
    const draggedCourseId = ref<number | null>(null);
    const dropTargetKey = ref<string | null>(null);
    const draggedCellDetails = ref<CellDetails | null>(null);
    const dragPreviewRef = ref<HTMLElement | null>(null);

    const isCoursePanelOpen = ref(initialCoursePanelOpen);
    const isCoursePanelContentVisible = ref(initialCoursePanelOpen);
    const COURSE_PANEL_MIN_WIDTH = 264;
    const COURSE_PANEL_MAX_WIDTH = 560;
    const COURSE_PANEL_TRANSITION_MS = 300;
    const coursePanelWidth = ref(320);
    const isCoursePanelResizing = ref(false);
    const coursePanelCardZoom = 'normal';

    const clampCoursePanelWidth = (value: number) => {
        return Math.min(COURSE_PANEL_MAX_WIDTH, Math.max(COURSE_PANEL_MIN_WIDTH, value));
    };

    const coursePanelStyle = computed(() => {
        return {
            width: isCoursePanelOpen.value
                ? `${coursePanelWidth.value}px`
                : '0px',
        };
    });

    let resizeStartX = 0;
    let resizeStartWidth = coursePanelWidth.value;
    let coursePanelContentTimer: number | null = null;

    const clearCoursePanelTimer = () => {
        if (coursePanelContentTimer !== null) {
            window.clearTimeout(coursePanelContentTimer);
            coursePanelContentTimer = null;
        }
    };

    const toggleCoursePanel = () => {
        const nextOpenState = !isCoursePanelOpen.value;

        clearCoursePanelTimer();

        if (nextOpenState) {
            isCoursePanelOpen.value = true;
            isCoursePanelContentVisible.value = false;
            coursePanelContentTimer = window.setTimeout(() => {
                isCoursePanelContentVisible.value = true;
                coursePanelContentTimer = null;
            }, COURSE_PANEL_TRANSITION_MS);
            return;
        }

        stopCoursePanelResize();
        isCoursePanelContentVisible.value = false;
        isCoursePanelOpen.value = false;
    };

    const onCoursePanelResizeStart = (event: MouseEvent) => {
        if (!isCoursePanelOpen.value) {
            return;
        }

        event.preventDefault();
        isCoursePanelResizing.value = true;
        resizeStartX = event.clientX;
        resizeStartWidth = coursePanelWidth.value;
        document.body.classList.add('select-none', 'cursor-col-resize');
    };

    const onWindowMouseMove = (event: MouseEvent) => {
        if (!isCoursePanelResizing.value) {
            return;
        }

        const delta = resizeStartX - event.clientX;
        coursePanelWidth.value = clampCoursePanelWidth(resizeStartWidth + delta);
    };

    const stopCoursePanelResize = () => {
        if (!isCoursePanelResizing.value) {
            return;
        }

        isCoursePanelResizing.value = false;
        document.body.classList.remove('select-none', 'cursor-col-resize');
    };

    const isCellOccupied = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return cellDetailsMap.value.has(buildCellKey(roomId, dateKey, period));
    };

    const findAssignmentIndex = (roomId: number, dateKey: string, period: AssignmentPeriod) => {
        return assignments.value.findIndex((assignment) => {
            return assignment.room_id === roomId
                && assignment.date === dateKey
                && assignment.period === period;
        });
    };

    const updateAssignmentSlot = (
        assignment: PersistedAssignment,
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
        draggedCourseId.value = null;
        dropTargetKey.value = null;
        draggedCellDetails.value = null;
    };

    const clearRangeReloadTimer = () => {
        if (rangeReloadTimer !== null) {
            window.clearTimeout(rangeReloadTimer);
            rangeReloadTimer = null;
        }
    };

    const reloadRange = (from: string, to: string) => {
        isRangeLoading.value = true;

        router.get(
            schedule({
                query: {
                    from,
                    to,
                },
            }).url,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => {
                    isRangeLoading.value = false;
                },
            },
        );
    };

    const scheduleRangeReload = () => {
        if (!fromDateInput.value || !toDateInput.value) {
            return;
        }

        let normalizedFrom = fromDateInput.value;
        let normalizedTo = toDateInput.value;

        if (normalizedFrom > normalizedTo) {
            [normalizedFrom, normalizedTo] = [normalizedTo, normalizedFrom];
            fromDateInput.value = normalizedFrom;
            toDateInput.value = normalizedTo;
        }

        if (normalizedFrom === props.fromDate && normalizedTo === props.toDate) {
            return;
        }

        clearRangeReloadTimer();
        rangeReloadTimer = window.setTimeout(() => {
            reloadRange(normalizedFrom, normalizedTo);
            rangeReloadTimer = null;
        }, 250);
    };

    const onDateRangeChange = (payload: { from: string; to: string }) => {
        fromDateInput.value = payload.from;
        toDateInput.value = payload.to;
        scheduleRangeReload();
    };


    const getCsrfToken = () => {
        const fromMeta = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (fromMeta) {
            return fromMeta;
        }

        const xsrfCookie = document.cookie
            .split('; ')
            .find((entry) => entry.startsWith('XSRF-TOKEN='));

        if (!xsrfCookie) {
            return '';
        }

        return decodeURIComponent(xsrfCookie.split('=').slice(1).join('='));
    };

    const parseResponseError = async (response: Response, fallbackMessage: string) => {
        try {
            const payload = await response.json() as { message?: string };
            return payload.message ?? fallbackMessage;
        }
        catch {
            return fallbackMessage;
        }
    };

    const persistAssignmentMove = async (
        assignmentId: number,
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
    ) => {
        const response = await fetch(`/scheduler/assignments/${assignmentId}`, {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                room_id: roomId,
                date: dateKey,
                period,
            }),
        });

        if (!response.ok) {
            throw new Error(await parseResponseError(response, 'La mise a jour du planning a echoue.'));
        }

        const payload = await response.json() as { assignment: PersistedAssignment };

        return payload.assignment;
    };

    const persistAssignmentCreate = async (
        courseId: number,
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
    ) => {
        const response = await fetch('/scheduler/assignments', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                course_id: courseId,
                room_id: roomId,
                date: dateKey,
                period,
            }),
        });

        if (!response.ok) {
            throw new Error(await parseResponseError(response, 'La creation du cours a echoue.'));
        }

        const payload = await response.json() as { assignment: PersistedAssignment };

        return payload.assignment;
    };

    const showError = (error: unknown, fallbackMessage: string) => {
        const message = error instanceof Error
            ? error.message
            : fallbackMessage;

        window.alert(message);
    };

    const BASE_CELL_WIDTH = 288;
    const BASE_CELL_HEIGHT = 96;

    const roomColWidth = '176px';

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
    } = useSchedulerView(initialZoom);

    const cellWidth = computed(() => `${Math.round(BASE_CELL_WIDTH * zoomRatio.value)}px`);
    const cellHeightRatio = computed(() => 0.75 + (zoomRatio.value - 0.5) * 0.5);
    const cellHeight = computed(() => `${Math.round(BASE_CELL_HEIGHT * cellHeightRatio.value)}px`);
    const dateGroupWidth = computed(() => `${Math.round(BASE_CELL_WIDTH * zoomRatio.value) * periods.length}px`);
    const dragPreviewWidth = computed(() => Math.min(Math.round(BASE_CELL_WIDTH * zoomRatio.value) - 8, 280));

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
        draggedCourseId.value = null;
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

    const onCourseDragStart = async (course: Course, event: DragEvent) => {
        draggedAssignmentKey.value = null;
        draggedCourseId.value = course.id;
        dropTargetKey.value = null;
        draggedCellDetails.value = getCourseDetails(course);

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/plain', String(course.id));
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
        const hasAssignmentDrag = draggedAssignmentKey.value !== null;
        const hasCourseDrag = draggedCourseId.value !== null;

        if (!hasAssignmentDrag && !hasCourseDrag) {
            return;
        }

        const sourceKey = draggedAssignmentKey.value;
        const targetKey = buildCellKey(roomId, dateKey, period);
        const isSameCell = sourceKey === targetKey;
        const targetUsed = isCellOccupied(roomId, dateKey, period);
        const canDrop = hasAssignmentDrag
            ? isSameCell || !targetUsed
            : !targetUsed;

        if (!canDrop) {
            dropTargetKey.value = null;

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'none';
            }

            return;
        }

        event.preventDefault();
        dropTargetKey.value = targetKey;

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = hasAssignmentDrag ? 'move' : 'copy';
        }
    };

    const onCellDrop = async (
        roomId: number,
        dateKey: string,
        period: AssignmentPeriod,
        event: DragEvent,
    ) => {
        event.preventDefault();

        const sourceKey = draggedAssignmentKey.value;
        const sourceCourseId = draggedCourseId.value;
        const targetKey = buildCellKey(roomId, dateKey, period);
        const isSameCell = sourceKey === targetKey;
        const targetUsed = isCellOccupied(roomId, dateKey, period);

        if (!sourceKey && sourceCourseId === null) {
            clearDragState();
            return;
        }

        if (sourceCourseId !== null) {
            if (targetUsed) {
                clearDragState();
                return;
            }

            try {
                const createdAssignment = await persistAssignmentCreate(sourceCourseId, roomId, dateKey, period);
                assignments.value = [...assignments.value, createdAssignment];
            }
            catch (error) {
                showError(error, 'La creation du cours a echoue.');
            }

            clearDragState();
            return;
        }

        if (!sourceKey || isSameCell || targetUsed) {
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

        if (!sourceAssignment.id || sourceAssignment.room_id == null) {
            clearDragState();
            return;
        }

        const previousRoomId = sourceAssignment.room_id;
        const previousDate = sourceAssignment.date;
        const previousPeriod = sourceAssignment.period;

        updateAssignmentSlot(sourceAssignment, roomId, dateKey, period);
        assignments.value = [...assignments.value];

        try {
            const persistedAssignment = await persistAssignmentMove(sourceAssignment.id, roomId, dateKey, period);
            assignments.value[sourceIndex] = persistedAssignment;
            assignments.value = [...assignments.value];
        }
        catch (error) {
            updateAssignmentSlot(sourceAssignment, previousRoomId, previousDate, previousPeriod);
            assignments.value = [...assignments.value];
            showError(error, 'La mise a jour du planning a echoue.');
        }

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
        forceSync,
    } = useSyncScroll();

    onMounted(() => {
        forceSync();
        window.addEventListener('mousemove', onWindowMouseMove);
        window.addEventListener('mouseup', stopCoursePanelResize);
    });

    onBeforeUnmount(() => {
        clearCoursePanelTimer();
        clearRangeReloadTimer();
        window.removeEventListener('mousemove', onWindowMouseMove);
        window.removeEventListener('mouseup', stopCoursePanelResize);
        document.body.classList.remove('select-none', 'cursor-col-resize');
    });

    watch(
        () => props.assignments,
        (value) => {
            assignments.value = [...value as PersistedAssignment[]];
        },
    );

    watch(
        () => [props.fromDate, props.toDate],
        ([fromDate, toDate]) => {
            fromDateInput.value = fromDate;
            toDateInput.value = toDate;
        },
    );

    watch(zoom, () => {
        forceSync();
    });

    watch(zoom, (value) => {
        writeCookie(SCHEDULER_ZOOM_COOKIE, zoomToCookieValue(value));
    });

    watch(isCoursePanelOpen, (isOpen) => {
        writeCookie(SCHEDULER_DRAWER_COOKIE, isOpen ? '1' : '0');
    });
</script>

<template>
    <div :class="[
        'overflow-hidden border border-zinc-300/60 bg-linear-to-br from-zinc-50 via-white to-zinc-100 dark:border-zinc-700/60 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900',
        isFullscreen
            ? 'fixed inset-0 z-50 rounded-none'
            : 'relative h-[calc(100vh-8rem)] min-h-112 max-h-[calc(100vh-8rem)] rounded-xl w-full',
    ]">
        <div class="flex h-full min-h-0">
            <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                <div
                    class="shrink-0 border-b border-zinc-300/70 bg-white/65 px-3 py-2 dark:border-zinc-700/70 dark:bg-zinc-900/55">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <SchedulerDateRangePicker :from-date="fromDateInput" :to-date="toDateInput"
                                @change="onDateRangeChange" />
                        </div>

                        <div class="flex items-center justify-end gap-1.5">
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

                            <button type="button"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-300/80 bg-zinc-900 px-3 py-1.5 text-xs font-semibold tracking-wide text-zinc-100 transition-colors hover:bg-zinc-800 dark:border-zinc-600/80 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                @click="toggleCoursePanel">
                                <BookOpen class="h-3.5 w-3.5" />
                                {{ isCoursePanelOpen ? 'Masquer les cours' : 'Afficher les cours' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex min-h-0 flex-1">
                    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
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

                            <div ref="gridScrollerRef" class="min-h-0 min-w-0 flex-1 overflow-auto"
                                @scroll="onGridScroll">
                                <div class="min-w-max">
                                    <div v-for="room in rooms" :key="`row-${room.id}`" class="flex">
                                        <template v-for="date in dates" :key="`${room.id}-${date.key}`">
                                            <div v-for="period in periods" :key="`${room.id}-${date.key}-${period.key}`"
                                                class="group relative border-r border-b border-zinc-100 bg-white px-0 py-0 transition-colors dark:border-zinc-800 dark:bg-zinc-950"
                                                :class="isDropTarget(room.id, date.key, period.key) ? 'bg-zinc-100/80 dark:bg-zinc-900/80' : ''"
                                                :style="{ width: cellWidth, height: cellHeight }"
                                                @dragover="onCellDragOver(room.id, date.key, period.key, $event)"
                                                @drop="onCellDrop(room.id, date.key, period.key, $event)">
                                                <SchedulerAssignmentCard
                                                    v-if="getCellDetails(room.id, date.key, period.key)"
                                                    :details="getCellDetails(room.id, date.key, period.key)!"
                                                    :zoom="zoom"
                                                    :is-dragged="isDraggedAssignment(room.id, date.key, period.key)"
                                                    @dragstart="onAssignmentDragStart(room.id, date.key, period.key, $event)"
                                                    @dragend="clearDragState()" />

                                                <div v-else class="relative flex h-full items-center justify-center">
                                                    <PlaceholderPattern
                                                        v-if="!isDropTarget(room.id, date.key, period.key)" />
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside
                        class="relative flex h-full shrink-0 flex-col border-l border-zinc-300/70 bg-zinc-50/70 transition-[width,opacity] duration-300 ease-out dark:border-zinc-700/70 dark:bg-zinc-900/45"
                        :class="[
                            isCoursePanelOpen ? 'opacity-100' : 'opacity-0',
                            isCoursePanelResizing ? 'transition-none' : '',
                        ]" :style="coursePanelStyle">
                        <button v-if="isCoursePanelOpen" type="button"
                            class="absolute inset-y-0 left-0 z-20 w-2 -translate-x-1/2 cursor-col-resize"
                            aria-label="Redimensionner la bibliotheque des cours"
                            @mousedown="onCoursePanelResizeStart" />

                        <div class="flex h-full min-w-0 flex-col transition-opacity duration-200"
                            :class="isCoursePanelContentVisible ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'">
                            <div class="shrink-0 border-b border-zinc-200/70 px-3 py-2 dark:border-zinc-700/70">
                                <p
                                    class="text-xs font-semibold tracking-wide text-zinc-600 uppercase dark:text-zinc-300">
                                    Bibliotheque des cours
                                </p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    Faites glisser une carte vers une case vide.
                                </p>
                            </div>

                            <div class="min-h-0 flex-1 overflow-y-auto p-2">
                                <div class="space-y-1.5">
                                    <div v-for="course in courses" :key="`course-${course.id}`" class="h-24">
                                        <SchedulerAssignmentCard :details="getCourseDetails(course)"
                                            :zoom="coursePanelCardZoom" :is-dragged="draggedCourseId === course.id"
                                            @dragstart="onCourseDragStart(course, $event)"
                                            @dragend="clearDragState()" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <div v-if="isRangeLoading"
            class="absolute inset-0 z-40 flex items-center justify-center bg-white/55 backdrop-blur-[1px] dark:bg-zinc-950/55">
            <div
                class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white/90 px-3 py-2 text-sm font-medium text-zinc-700 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/90 dark:text-zinc-200">
                <span
                    class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-700 dark:border-zinc-600 dark:border-t-zinc-200" />
                Chargement du planning...
            </div>
        </div>

        <Teleport to="body">
            <div v-if="draggedCellDetails" ref="dragPreviewRef" class="fixed pointer-events-none opacity-95 -rotate-2"
                :style="{ top: '-9999px', left: '-9999px', zIndex: 9999 }">
                <SchedulerDragPreview :details="draggedCellDetails" :zoom="zoom" :width="dragPreviewWidth" />
            </div>
        </Teleport>
    </div>
</template>
