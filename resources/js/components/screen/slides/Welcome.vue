<script setup>
    import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

    const props = defineProps({ data: Object });
    const emit = defineEmits(['next']);

    const minimumDuration = computed(() => props.data?.minimumDuration ?? props.data?.duration ?? 5000);
    const isReady = computed(() => props.data?.isReady ?? true);
    const minimumDurationElapsed = ref(false);

    let hasEmitted = false;

    const dateLabel = new Date().toLocaleDateString('fr-BE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    let timer = null;

    function emitNextIfReady() {
        if (hasEmitted || !minimumDurationElapsed.value || !isReady.value) {
            return;
        }

        hasEmitted = true;
        emit('next');
    }

    onMounted(() => {
        timer = window.setTimeout(() => {
            minimumDurationElapsed.value = true;
            emitNextIfReady();
        }, minimumDuration.value);
    });

    watch(isReady, () => {
        emitNextIfReady();
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
