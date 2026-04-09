<script setup>
    import { onMounted, onUnmounted } from 'vue';

    const props = defineProps({ data: Object });
    const emit = defineEmits(['next']);

    const dateLabel = new Date().toLocaleDateString('fr-BE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    let timer = null;

    onMounted(() => {
        timer = window.setTimeout(() => emit('next'), props.data.duration);
    });

    onUnmounted(() => {
        if (timer !== null) window.clearTimeout(timer);
    });
</script>

<template>
    <div class="w-screen h-screen bg-[#1e2d55] font-sans overflow-hidden flex items-center justify-center">

        <!-- Content -->
        <div class="flex flex-col items-center gap-12 z-10">
            <img src="/IFO_Logo_NEGATIF.png" alt="Logo IFOSUP" class="h-36 object-contain">

            <div class="text-center">
                <div class="mt-5 flex items-center justify-center gap-4">
                    <div class="h-px w-16 bg-[#f2ae35]/50"></div>
                    <p class="text-[#f2ae35] text-xl font-bold uppercase tracking-[0.3em]">{{ dateLabel }}
                    </p>
                    <div class="h-px w-16 bg-[#f2ae35]/50"></div>
                </div>
            </div>
        </div>
    </div>
</template>
