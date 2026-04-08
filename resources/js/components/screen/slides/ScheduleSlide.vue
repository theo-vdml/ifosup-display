<script setup>
    import { computed, onMounted, onUnmounted } from 'vue';

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

    let timer = null;

    onMounted(() => {
        timer = window.setTimeout(() => {
            emit('next');
        }, props.data.duration);
    });

    onUnmounted(() => {
        if (timer !== null) {
            window.clearTimeout(timer);
        }
    });
</script>

<template>
    <div class="w-screen h-screen flex flex-col bg-gray-100 font-sans overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-14 py-8 bg-[#1e2d55] shrink-0">
            <div class="flex items-center gap-8">
                <img src="/IFO_Gimmick_SUPERIEUR.png" class="size-16">
                <div>
                    <p class="text-[#f2ae35] text-base font-semibold uppercase tracking-[0.3em] mb-0.5">Planning</p>
                    <h1 class="text-white text-5xl font-black leading-tight uppercase">Cours du soir</h1>
                </div>
            </div>
            <p class="text-[#f2ae35] text-base font-medium uppercase tracking-widest">{{ dateLabel }}</p>
        </div>

        <!-- Column headers -->
        <div class="grid grid-cols-[3fr_2fr_2fr_1fr] gap-6 px-14 py-6 shrink-0 bg-[#f2ae35]">
            <span class="text-black text-lg font-extrabold uppercase tracking-widest">Professeur</span>
            <span class="text-black text-lg font-extrabold uppercase tracking-widest">Sections</span>
            <span class="text-black text-lg font-extrabold uppercase tracking-widest">Local</span>
            <span class="text-black text-lg font-extrabold uppercase tracking-widest">Cours</span>
        </div>

        <!-- Rows -->
        <div class="flex-1 flex flex-col px-14 overflow-hidden">
            <div v-for="(row, index) in data.rows" :key="row.id ?? index"
                class="grid grid-cols-[3fr_2fr_2fr_1fr] gap-6 items-center py-5">
                <!-- Course -->
                <div class="min-w-0">
                    <p class="text-gray-900 text-2xl font-bold leading-tight truncate">{{ row.course?.name }}</p>
                    <p v-if="row.course?.code"
                        class="text-[#1e2d55] text-base font-bold uppercase tracking-widest leading-none mt-2 italic">
                        {{ row.course.code }}
                    </p>
                </div>

                <!-- Teacher -->
                <span class="text-gray-700 text-xl font-semibold truncate">{{ row.course?.teacher?.name ?? '—' }}</span>

                <!-- Groups -->
                <span class="text-gray-700 text-xl font-semibold truncate">
                    {{row.course?.groups?.map(g => g.name).join(', ') || '—'}}
                </span>

                <!-- Room -->
                <span class="text-gray-700 text-xl font-semibold">{{ row.room?.name ?? '—' }}</span>
            </div>

            <!-- Empty state -->
            <div v-if="!data.rows?.length" class="flex-1 flex items-center justify-center">
                <p class="text-gray-400 text-2xl font-semibold">Aucun cours prévu pour le moment.</p>
            </div>
        </div>
    </div>
</template>
