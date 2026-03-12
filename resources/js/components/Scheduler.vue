<script setup lang="ts">
    import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
    import { DoorOpen, Plus } from 'lucide-vue-next';

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

    const ROOM_COUNT = 50;
    const fromDate = '2026-03-10';
    const toDate = '2026-03-30';

    const ROOM_COL_WIDTH = 176;
    const CELL_WIDTH = 288;

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

    const headerTrackRef = ref<HTMLElement | null>(null);
    const roomTrackRef = ref<HTMLElement | null>(null);
    const gridScrollerRef = ref<HTMLElement | null>(null);

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

    onMounted(() => {
        const scroller = gridScrollerRef.value;
        if (!scroller) {
            return;
        }

        pendingLeft = Math.round(scroller.scrollLeft);
        pendingTop = Math.round(scroller.scrollTop);
        syncTracks();
    });

    onBeforeUnmount(() => {
        if (animationFrameId !== null) {
            cancelAnimationFrame(animationFrameId);
        }
    });

    const dateGroupWidth = `${periods.length * CELL_WIDTH}px`;
    const roomColWidth = `${ROOM_COL_WIDTH}px`;
    const cellWidth = `${CELL_WIDTH}px`;
</script>

<template>
    <div
        class="h-[calc(100vh-8rem)] min-h-112 max-h-[calc(100vh-8rem)] w-full overflow-hidden border border-zinc-300/60 bg-linear-to-br from-zinc-50 via-white to-zinc-100 dark:border-zinc-700/60 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-900">
        <div class="flex h-full min-h-0 flex-col">
            <div class="shrink-0 border-b border-zinc-300/70 dark:border-zinc-700/70">
                <div class="flex">
                    <div class="h-22 shrink-0 bg-zinc-300 px-4 align-middle text-xs font-semibold tracking-wide text-zinc-900 uppercase dark:bg-zinc-800 dark:text-zinc-100 flex items-center justify-center gap-2"
                        :style="{ width: roomColWidth }">
                        <DoorOpen class="h-4 w-4" />
                        Locaux
                    </div>

                    <div class="relative flex-1 overflow-hidden">
                        <div ref="headerTrackRef" class="min-w-max transform-gpu will-change-transform">
                            <div class="flex">
                                <div v-for="date in dates" :key="`date-${date.key}`"
                                    class="h-12 border-r border-b border-zinc-300/80 bg-zinc-200 px-3 text-center text-xs font-semibold tracking-wide text-zinc-800 uppercase dark:border-zinc-700 dark:bg-zinc-700 dark:text-zinc-200 flex items-center justify-center"
                                    :style="{ width: dateGroupWidth }">
                                    {{ date.label }}
                                </div>
                            </div>

                            <div class="flex">
                                <template v-for="date in dates" :key="`periods-${date.key}`">
                                    <div v-for="period in periods" :key="`${date.key}-${period.key}`"
                                        class="h-10 border-r border-zinc-200/80 bg-zinc-100 px-3 text-center text-[11px] font-semibold tracking-wide text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 flex items-center justify-center"
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
                            class="h-24 border-b border-zinc-200 bg-zinc-100 px-4 py-2.5 text-left font-medium text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 flex items-center">
                            {{ room.name }}
                        </div>
                    </div>
                </div>

                <div ref="gridScrollerRef" class="min-h-0 min-w-0 flex-1 overflow-auto" @scroll="onGridScroll">
                    <div class="min-w-max">
                        <div v-for="room in rooms" :key="`row-${room.id}`" class="flex">
                            <template v-for="date in dates" :key="`${room.id}-${date.key}`">
                                <div v-for="period in periods" :key="`${room.id}-${date.key}-${period.key}`"
                                    class="group relative h-24 border-r border-b border-zinc-200 bg-white px-0 py-0 dark:border-zinc-800 dark:bg-zinc-950"
                                    :style="{ width: cellWidth }">
                                    <div v-if="getUsage(room.id, date.key, period.key)"
                                        class="m-1 flex h-[calc(100%-0.5rem)] items-center rounded-md border border-emerald-400 bg-emerald-50 px-3 py-3 shadow-sm dark:border-emerald-600 dark:bg-emerald-900/30">
                                        <div
                                            class="text-xs leading-tight font-semibold text-emerald-900 wrap-break-word dark:text-emerald-100">
                                            {{ getUsage(room.id, date.key, period.key) }}
                                        </div>
                                    </div>

                                    <div v-else class="flex h-full items-center justify-center">
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
    </div>
</template>
