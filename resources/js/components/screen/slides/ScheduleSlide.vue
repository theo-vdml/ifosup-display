<script setup>
    import { computed, onMounted, onUnmounted, ref } from 'vue';

    const props = defineProps({ data: Object });
    const emit = defineEmits(['next']);

    const now = new Date();

    const dateLabel = now.toLocaleDateString('fr-BE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    const timeLabel = ref('');

    function updateTimeLabel() {
        timeLabel.value = new Date().toLocaleTimeString('fr-BE', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });
    }

    const title = computed(() => props.data?.title ?? 'Planning des cours');

    function compareRooms(a, b) {
        const isInt = (s) => /^-?\d+$/.test(s);
        const na = parseInt(a, 10), nb = parseInt(b, 10);
        const group = (s, n) => !isInt(s) ? 2 : n < 0 ? 0 : 1;
        const ga = group(a, na), gb = group(b, nb);
        if (ga !== gb) return ga - gb;
        if (ga === 0) return Math.abs(na) - Math.abs(nb);
        if (ga === 1) return na - nb;
        return a.localeCompare(b);
    }

    const sortedRows = computed(() => {
        const rows = props.data?.rows ?? [];
        return [...rows].sort((a, b) => compareRooms(a.room?.name ?? '', b.room?.name ?? ''));
    });

    // Auto-scroll via transform
    const outerContainer = ref(null);
    const innerContent = ref(null);
    const translateY = ref(0);
    const scrollProgress = ref(0);
    const barWidth = ref(100);

    const SPEED = 0.6;
    const INITIAL_PAUSE_FRAMES = 300;  // 5s at 60fps
    const BOTTOM_PAUSE_FRAMES = 300;  // 5s at 60fps
    const NO_SCROLL_FRAMES = 600;     // 10s at 60fps

    // phases: 'initial-wait' → next               (no content)
    //         'initial-wait' → 'no-scroll-wait' → next  (content, no scroll)
    //         'initial-wait' → 'scrolling' → 'bottom-wait' → next  (scroll)
    let phase = 'initial-wait';
    let phaseFrames = INITIAL_PAUSE_FRAMES;
    let animationId = null;
    let timeIntervalId = null;

    function animate() {
        const outer = outerContainer.value;
        const inner = innerContent.value;
        if (!outer || !inner) { animationId = requestAnimationFrame(animate); return; }

        const maxTranslate = inner.offsetHeight - outer.offsetHeight;
        barWidth.value = outer.offsetHeight / inner.offsetHeight * 100;

        if (phase === 'initial-wait') {
            if (--phaseFrames <= 0) {
                if (!props.data?.rows?.length) { emit('next'); return; }
                if (maxTranslate > 0) {
                    phase = 'scrolling';
                } else {
                    phase = 'no-scroll-wait';
                    phaseFrames = NO_SCROLL_FRAMES;
                }
            }
        } else if (phase === 'scrolling') {
            translateY.value = Math.min(translateY.value + SPEED, maxTranslate);
            scrollProgress.value = translateY.value / maxTranslate;
            if (translateY.value >= maxTranslate) {
                phase = 'bottom-wait';
                phaseFrames = BOTTOM_PAUSE_FRAMES;
            }
        } else if (phase === 'bottom-wait' || phase === 'no-scroll-wait') {
            if (--phaseFrames <= 0) {
                emit('next');
                return;
            }
        }

        animationId = requestAnimationFrame(animate);
    }

    onMounted(() => {
        updateTimeLabel();
        timeIntervalId = window.setInterval(updateTimeLabel, 1000);
        animationId = requestAnimationFrame(animate);
    });

    onUnmounted(() => {
        if (animationId !== null) cancelAnimationFrame(animationId);
        if (timeIntervalId !== null) clearInterval(timeIntervalId);
    });
</script>

<template>
    <div class="w-screen h-screen flex flex-col bg-gray-100 font-sans overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-14 py-8 bg-[#1e2d55] shrink-0">
            <div class="flex items-center gap-8">
                <img src="/IFO_Gimmick_SUPERIEUR.png" class="size-16">
                <div>
                    <p class="text-[#f2ae35] text-base font-semibold uppercase tracking-[0.3em] mb-0.5">Locaux</p>
                    <h1 class="text-white text-5xl font-black leading-tight uppercase">{{ title }}</h1>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[#f2ae35] text-lg font-bold uppercase tracking-widest">{{ dateLabel }}</p>
                <p class="text-white text-3xl font-black tracking-[0.2em] mt-2">{{ timeLabel }}</p>
            </div>
        </div>

        <!-- Column headers -->
        <div v-if="sortedRows.length" class="grid grid-cols-[2fr_2fr_2fr_1fr] gap-6 px-14 py-6 shrink-0 bg-[#f2ae35]">
            <span class="text-black text-lg font-black uppercase tracking-widest">Cours</span>
            <span class="text-black text-lg font-black uppercase tracking-widest">Professeur</span>
            <span class="text-black text-lg font-black uppercase tracking-widest">Sections</span>
            <span class="text-black text-lg font-black uppercase tracking-widest">Local</span>
        </div>

        <!-- Rows -->
        <div class="flex-1 overflow-hidden min-h-0">
            <div ref="outerContainer" class="h-full overflow-hidden relative">
                <div ref="innerContent" :style="{ transform: `translateY(-${translateY}px)` }">
                    <div v-for="(row, index) in sortedRows" :key="row.id ?? index"
                        class="grid grid-cols-[2fr_2fr_2fr_1fr] gap-6 items-center py-8 px-14 border-b border-gray-300"
                        :class="row.status === 'cancelled' ? 'bg-red-50' : row.status === 'late' ? 'bg-orange-50' : index % 2 === 0 ? 'bg-white' : ''">
                        <!-- Course -->
                        <div class="min-w-0">
                            <p class="text-2xl font-bold leading-tight truncate"
                                :class="row.status === 'cancelled' ? 'text-red-600' : 'text-gray-900'">{{
                                    row.course?.name }}
                            </p>
                            <p v-if="row.course?.code"
                                class="text-base font-bold uppercase tracking-widest leading-none mt-2 italic"
                                :class="row.status === 'cancelled' ? 'text-red-400' : 'text-[#1e2d55]'">
                                {{ row.course.code }}
                            </p>
                        </div>

                        <!-- Teacher -->
                        <span class="inline-flex items-center gap-3 text-xl font-semibold truncate"
                            :class="row.status === 'cancelled' ? 'text-red-600' : row.status === 'late' ? 'text-orange-400' : 'text-gray-700'">
                            <span>{{
                                row.course?.teacher?.name ?? '—' }}</span>
                            <span v-if="row.status === 'late'"
                                class="shrink-0 rounded-full bg-orange-100 border border-orange-300 px-3 py-0.5 text-sm font-semibold text-orange-700">
                                En retard
                            </span>
                        </span>

                        <!-- Groups -->
                        <span class="text-xl font-semibold truncate"
                            :class="row.status === 'cancelled' ? 'text-red-600' : 'text-gray-700'">
                            {{row.course?.groups?.map(g => g.name).join(', ') || '—'}}
                        </span>

                        <!-- Room -->
                        <span v-if="row.status === 'cancelled'"
                            class="text-red-600 text-2xl font-bold uppercase tracking-wide">
                            Annulé
                        </span>
                        <span v-else class="text-gray-700 text-2xl font-semibold">
                            {{ row.room?.name ?? '—' }}
                        </span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!sortedRows.length" class="flex flex-col items-center justify-center gap-6"
                        :style="{ height: outerContainer?.offsetHeight + 'px' }">
                        <img src="/empty.svg" class="size-96 opacity-60" alt="">
                        <p class="text-gray-400 text-2xl font-semibold">Aucun cours prévu pour le moment.</p>
                    </div>
                </div>

                <!-- Scrollbar — absolute overlay, right edge -->
                <div v-if="barWidth < 100" class="absolute top-4 bottom-4 right-4 w-1.5 rounded-full bg-black/10">
                    <div class="absolute inset-x-0 rounded-full bg-[#1e2d55]"
                        :style="{ height: barWidth + '%', top: (scrollProgress * (100 - barWidth)) + '%' }"></div>
                </div>
            </div>
        </div>
    </div>
</template>
