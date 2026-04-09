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

    const periodLabel = computed(() => {
        const h = now.getHours();
        if (h < 12) return 'Cours du matin';
        if (h < 17) return "Cours de l'après-midi";
        return 'Cours du soir';
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
    const NO_SCROLL_FRAMES = 600;  // 10s if content already fits

    // phases: 'initial-wait' → 'scrolling' → 'bottom-wait' → done
    //         'initial-wait' → 'no-scroll-wait' → done  (when nothing to scroll)
    let phase = 'initial-wait';
    let phaseFrames = INITIAL_PAUSE_FRAMES;
    let animationId = null;

    function animate() {
        const outer = outerContainer.value;
        const inner = innerContent.value;
        if (!outer || !inner) { animationId = requestAnimationFrame(animate); return; }

        const maxTranslate = inner.offsetHeight - outer.offsetHeight;
        barWidth.value = outer.offsetHeight / inner.offsetHeight * 100;

        if (phase === 'initial-wait') {
            if (--phaseFrames <= 0) {
                phase = maxTranslate > 0 ? 'scrolling' : 'no-scroll-wait';
                phaseFrames = NO_SCROLL_FRAMES;
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
        animationId = requestAnimationFrame(animate);
    });

    onUnmounted(() => {
        if (animationId !== null) cancelAnimationFrame(animationId);
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
                    <h1 class="text-white text-5xl font-black leading-tight uppercase">Cours du soir</h1>
                </div>
            </div>
            <p class="text-[#f2ae35] text-lg font-bold uppercase tracking-widest">{{ dateLabel }}</p>
        </div>

        <!-- Column headers -->
        <div class="grid grid-cols-[2fr_2fr_2fr_1fr] gap-6 px-14 py-6 shrink-0 bg-[#f2ae35]">
            <span class="text-black text-lg font-black uppercase tracking-widest">Cours</span>
            <span class="text-black text-lg font-black uppercase tracking-widest">Professeur</span>
            <span class="text-black text-lg font-black uppercase tracking-widest">Sections</span>
            <span class="text-black text-lg font-black uppercase tracking-widest">Local</span>
        </div>

        <!-- Rows -->
        <div class="flex-1 overflow-hidden min-h-0">
            <div ref="outerContainer" class="h-full overflow-hidden relative">
                <div ref="innerContent" :style="{ transform: `translateY(-${translateY}px)` }">
                    <div v-for="(row, index) in data.rows" :key="row.id ?? index"
                        class="grid grid-cols-[2fr_2fr_2fr_1fr] gap-6 items-center py-8 px-14"
                        :class="index % 2 === 0 ? 'bg-white' : ''">
                        <!-- Course -->
                        <div class="min-w-0">
                            <p class="text-gray-900 text-2xl font-bold leading-tight truncate">{{ row.course?.name }}
                            </p>
                            <p v-if="row.course?.code"
                                class="text-[#1e2d55] text-base font-bold uppercase tracking-widest leading-none mt-2 italic">
                                {{ row.course.code }}
                            </p>
                        </div>

                        <!-- Teacher -->
                        <span class="text-gray-700 text-xl font-semibold truncate">{{ row.course?.teacher?.name ?? '—'
                        }}</span>

                        <!-- Groups -->
                        <span class="text-gray-700 text-xl font-semibold truncate">
                            {{row.course?.groups?.map(g => g.name).join(', ') || '—'}}
                        </span>

                        <!-- Room -->
                        <span class="text-gray-700 text-2xl font-semibold">{{ row.room?.name ?? '—' }}</span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!data.rows?.length" class="flex items-center justify-center py-20 px-14">
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
