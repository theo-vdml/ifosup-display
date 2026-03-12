<script setup lang="ts">
    import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
    import { DoorOpen, Maximize2, Minimize2, Minus, Plus } from 'lucide-vue-next';
    import PlaceholderPattern from './PlaceholderPattern.vue';

    type PeriodKey = 'morning' | 'afternoon' | 'evening';

    type Room = {
        id: number;
        name: string;
    };

    type Occupation = {
        roomId: number;
        date: string;
        period: PeriodKey;
        groupName: string;
    };

    type GroupTheme = {
        cardBg: string;
        cardBorder: string;
        cardText: string;
        badgeBg: string;
        badgeText: string;
        accent: string;
    };

    type CellDetails = {
        groupLabel: string;
        courseLabel: string;
        theme: GroupTheme;
    };

    const ROOM_COUNT = 50;
    const fromDate = '2026-03-10';
    const toDate = '2026-03-30';

    type ZoomLevel = 'small' | 'normal' | 'large' | 'xl';

    const ZOOM_LEVELS: ZoomLevel[] = ['small', 'normal', 'large', 'xl'];
    const ZOOM_LABELS: Record<ZoomLevel, string> = {
        small: '0.5',
        normal: '1.0',
        large: '1.5',
        xl: '2.0',
    };

    const zoomConfig: Record<ZoomLevel, { cellWidth: number; roomColWidth: number; cellHeight: number }> = {
        small: { cellWidth: 148, roomColWidth: 128, cellHeight: 64 },
        normal: { cellWidth: 288, roomColWidth: 176, cellHeight: 96 },
        large: { cellWidth: 352, roomColWidth: 200, cellHeight: 112 },
        xl: { cellWidth: 448, roomColWidth: 240, cellHeight: 144 },
    };

    const periods: Array<{ key: PeriodKey; label: string }> = [
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

    const hashToUnit = (seed: string) => {
        let hash = 2166136261;

        for (let i = 0; i < seed.length; i += 1) {
            hash ^= seed.charCodeAt(i);
            hash = Math.imul(hash, 16777619);
        }

        return (hash >>> 0) / 4294967295;
    };

    const roomPrefixes = ['A', 'B', 'C', 'D', 'E'];

    const generateRooms = (count: number): Room[] => {
        return Array.from({ length: count }, (_, index) => {
            const id = index + 1;
            const prefix = roomPrefixes[index % roomPrefixes.length];
            const floor = Math.floor(index / 10) + 1;
            const roomNumber = String((index % 10) + 1).padStart(2, '0');

            return {
                id,
                name: `Local ${prefix}${floor}${roomNumber}`,
            };
        });
    };

    const groupPool = [
        'Groupe 1 - Mathematiques',
        'Groupe 2 - Physique',
        'Groupe 3 - Biologie',
        'Groupe 4 - Chimie',
        'Groupe 5 - Anglais',
        'Groupe 6 - Histoire',
        'Groupe 7 - Geographie',
        'Groupe 8 - Economie',
        'Groupe 9 - Informatique',
        'Groupe 10 - Reseaux',
        'Groupe 11 - Electronique',
        'Groupe 12 - Robotique',
    ];

    const generateOccupations = (
        roomList: Room[],
        dateKeys: string[],
        periodList: Array<{ key: PeriodKey; label: string }>,
    ): Occupation[] => {
        const items: Occupation[] = [];

        for (const room of roomList) {
            for (const date of dateKeys) {
                for (const period of periodList) {
                    const slotKey = `${room.id}|${date}|${period.key}`;
                    const occupancyChance = hashToUnit(slotKey);

                    if (occupancyChance < 0.15) {
                        const groupIndex = Math.floor(
                            hashToUnit(`${slotKey}|group`) * groupPool.length,
                        );

                        items.push({
                            roomId: room.id,
                            date,
                            period: period.key,
                            groupName: groupPool[groupIndex],
                        });
                    }
                }
            }
        }

        return items;
    };

    const dateKeys = buildDateKeys(fromDate, toDate);
    const rooms = generateRooms(ROOM_COUNT);
    const occupations = generateOccupations(rooms, dateKeys, periods);

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

    const occupationMap = computed(() => {
        const map = new Map<string, string>();

        for (const occupation of occupations) {
            const key = `${occupation.roomId}|${occupation.date}|${occupation.period}`;
            map.set(key, occupation.groupName);
        }

        return map;
    });

    const getUsage = (roomId: number, dateKey: string, period: PeriodKey) => {
        const key = `${roomId}|${dateKey}|${period}`;
        return occupationMap.value.get(key);
    };

    const groupBaseColors: Record<number, string> = {
        1: '#2563eb',
        2: '#0891b2',
        3: '#10b981',
        4: '#65a30d',
        5: '#ca8a04',
        6: '#ea580c',
        7: '#db2777',
        8: '#9333ea',
        9: '#7c3aed',
        10: '#1d4ed8',
        11: '#0d9488',
        12: '#57534e',
    };

    const clamp = (value: number) => Math.max(0, Math.min(255, Math.round(value)));

    const hexToRgb = (hex: string) => {
        const clean = hex.replace('#', '');
        const normalized = clean.length === 3
            ? clean.split('').map((c) => c + c).join('')
            : clean;
        const int = Number.parseInt(normalized, 16);

        return {
            r: (int >> 16) & 255,
            g: (int >> 8) & 255,
            b: int & 255,
        };
    };

    const mixRgb = (
        base: { r: number; g: number; b: number },
        target: { r: number; g: number; b: number },
        factor: number,
    ) => {
        return {
            r: clamp(base.r + (target.r - base.r) * factor),
            g: clamp(base.g + (target.g - base.g) * factor),
            b: clamp(base.b + (target.b - base.b) * factor),
        };
    };

    const rgbToCss = (rgb: { r: number; g: number; b: number }) => {
        return `rgb(${rgb.r}, ${rgb.g}, ${rgb.b})`;
    };

    const deriveThemeFromBase = (baseHex: string, dark: boolean): GroupTheme => {
        const base = hexToRgb(baseHex);
        const white = { r: 255, g: 255, b: 255 };
        const black = { r: 0, g: 0, b: 0 };

        if (dark) {
            return {
                accent: rgbToCss(mixRgb(base, white, 0.1)),
                cardBg: rgbToCss(mixRgb(base, black, 0.78)),
                cardBorder: rgbToCss(mixRgb(base, black, 0.38)),
                cardText: rgbToCss(mixRgb(base, white, 0.72)),
                badgeBg: rgbToCss(mixRgb(base, black, 0.58)),
                badgeText: rgbToCss(mixRgb(base, white, 0.64)),
            };
        }

        return {
            accent: rgbToCss(base),
            cardBg: rgbToCss(mixRgb(base, white, 0.82)),
            cardBorder: rgbToCss(mixRgb(base, black, 0.12)),
            cardText: rgbToCss(mixRgb(base, black, 0.55)),
            badgeBg: rgbToCss(mixRgb(base, white, 0.7)),
            badgeText: rgbToCss(mixRgb(base, black, 0.4)),
        };
    };

    const getGroupTheme = (groupLabel: string) => {
        const match = groupLabel.match(/groupe\s+(\d+)/i);

        if (match) {
            const number = Number.parseInt(match[1], 10);
            const baseColor = groupBaseColors[number] ?? '#334155';
            return deriveThemeFromBase(baseColor, isDarkMode.value);
        }

        return deriveThemeFromBase('#334155', isDarkMode.value);
    };

    const cellDetailsMap = computed(() => {
        const map = new Map<string, CellDetails>();

        for (const occupation of occupations) {
            const key = `${occupation.roomId}|${occupation.date}|${occupation.period}`;
            const [groupLabel, ...courseParts] = occupation.groupName.split(' - ');

            map.set(key, {
                groupLabel,
                courseLabel: courseParts.join(' - ') || 'Cours',
                theme: getGroupTheme(groupLabel),
            });
        }

        return map;
    });

    const getCellDetails = (roomId: number, dateKey: string, period: PeriodKey) => {
        const key = `${roomId}|${dateKey}|${period}`;
        return cellDetailsMap.value.get(key);
    };

    const headerTrackRef = ref<HTMLElement | null>(null);
    const roomTrackRef = ref<HTMLElement | null>(null);
    const gridScrollerRef = ref<HTMLElement | null>(null);
    const isDarkMode = ref(false);

    let themeObserver: MutationObserver | null = null;

    let animationFrameId: number | null = null;
    let pendingLeft = 0;
    let pendingTop = 0;
    let appliedLeft = -1;
    let appliedTop = -1;

    const syncTracks = () => {
        if (pendingLeft === appliedLeft && pendingTop === appliedTop) {
            animationFrameId = null;
            return;
        }

        if (headerTrackRef.value) {
            headerTrackRef.value.style.transform = `translateX(-${pendingLeft}px)`;
        }

        if (roomTrackRef.value) {
            roomTrackRef.value.style.transform = `translateY(-${pendingTop}px)`;
        }

        appliedLeft = pendingLeft;
        appliedTop = pendingTop;

        animationFrameId = null;
    };

    const onGridScroll = (event: Event) => {
        const target = event.target as HTMLElement;
        pendingLeft = Math.round(target.scrollLeft);
        pendingTop = Math.round(target.scrollTop);

        if (pendingLeft === appliedLeft && pendingTop === appliedTop) {
            return;
        }

        if (animationFrameId !== null) {
            return;
        }

        animationFrameId = requestAnimationFrame(syncTracks);
    };

    const refreshThemeMode = () => {
        isDarkMode.value = document.documentElement.classList.contains('dark');
    };

    onMounted(() => {
        refreshThemeMode();

        themeObserver = new MutationObserver(refreshThemeMode);
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        const scroller = gridScrollerRef.value;
        if (!scroller) {
            return;
        }

        pendingLeft = Math.round(scroller.scrollLeft);
        pendingTop = Math.round(scroller.scrollTop);
        syncTracks();
    });

    onBeforeUnmount(() => {
        if (themeObserver) {
            themeObserver.disconnect();
            themeObserver = null;
        }

        if (animationFrameId !== null) {
            cancelAnimationFrame(animationFrameId);
        }
    });

    const zoom = ref<ZoomLevel>('normal');

    const zoomIndex = computed(() => ZOOM_LEVELS.indexOf(zoom.value));
    const canZoomIn = computed(() => zoomIndex.value < ZOOM_LEVELS.length - 1);
    const canZoomOut = computed(() => zoomIndex.value > 0);

    const zoomIn = () => { if (canZoomIn.value) zoom.value = ZOOM_LEVELS[zoomIndex.value + 1]; };
    const zoomOut = () => { if (canZoomOut.value) zoom.value = ZOOM_LEVELS[zoomIndex.value - 1]; };

    const currentZoom = computed(() => zoomConfig[zoom.value]);
    const zoomLabel = computed(() => ZOOM_LABELS[zoom.value]);
    const dateGroupWidth = computed(() => `${periods.length * currentZoom.value.cellWidth}px`);
    const roomColWidth = computed(() => `${currentZoom.value.roomColWidth}px`);
    const cellWidth = computed(() => `${currentZoom.value.cellWidth}px`);
    const cellHeight = computed(() => `${currentZoom.value.cellHeight}px`);

    const isFullscreen = ref(false);
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
                    <div ref="roomTrackRef" class="transform-gpu will-change-transform">
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
                                    class="group relative border-r border-b border-zinc-100 bg-white px-0 py-0 dark:border-zinc-800 dark:bg-zinc-950"
                                    :style="{ width: cellWidth, height: cellHeight }">
                                    <div v-if="getCellDetails(room.id, date.key, period.key)"
                                        class="m-1 flex h-[calc(100%-0.5rem)] flex-col justify-between rounded-xl border shadow-[0_10px_20px_rgba(0,0,0,0.08)]"
                                        :class="zoom === 'small' ? 'px-2 py-1.5' : 'px-3 py-2'" :style="{
                                            backgroundColor: getCellDetails(room.id, date.key, period.key)?.theme.cardBg,
                                            borderColor: getCellDetails(room.id, date.key, period.key)?.theme.cardBorder,
                                        }">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="rounded-full font-bold uppercase tracking-wide truncate"
                                                :class="zoom === 'small' ? 'text-[8px] px-1.5 py-0' : 'text-[10px] px-2 py-0.5'"
                                                :style="{
                                                    backgroundColor: getCellDetails(room.id, date.key, period.key)?.theme.badgeBg,
                                                    color: getCellDetails(room.id, date.key, period.key)?.theme.badgeText,
                                                }">
                                                {{ getCellDetails(room.id, date.key, period.key)?.groupLabel }}
                                            </span>
                                            <span v-if="zoom !== 'small'" class="h-2.5 w-2.5 shrink-0 rounded-full"
                                                :style="{
                                                    backgroundColor: getCellDetails(room.id, date.key, period.key)?.theme.accent,
                                                }" />
                                        </div>

                                        <div class="leading-tight font-semibold wrap-break-word"
                                            :class="zoom === 'small' ? 'text-[10px]' : 'mt-2 text-sm'" :style="{
                                                color: getCellDetails(room.id, date.key, period.key)?.theme.cardText,
                                            }">
                                            {{ getCellDetails(room.id, date.key, period.key)?.courseLabel }}
                                        </div>

                                        <div v-if="zoom !== 'small'"
                                            class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                                            Secrétariat - créneau validé
                                        </div>
                                    </div>

                                    <div v-else class="flex h-full items-center justify-center relative">
                                        <PlaceholderPattern />
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
                @click="isFullscreen = !isFullscreen">
                <Maximize2 v-if="!isFullscreen" class="h-3.5 w-3.5" />
                <Minimize2 v-else class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>
</template>
