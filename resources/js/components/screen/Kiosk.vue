<template>
    <div class="h-screen w-screen bg-black overflow-hidden relative">

        <transition name="fade" mode="out-in">
            <component v-if="currentSlide" :is="components[currentSlide.type]" :key="currentIndex"
                :data="currentSlide.data" @next="goToNextSlide" />
        </transition>
    </div>
</template>

<script setup>
    import { ref, computed, defineAsyncComponent, onMounted } from 'vue';

    const components = {
        image: defineAsyncComponent(() => import('./slides/ImageSlide.vue')),
        video: defineAsyncComponent(() => import('./slides/VideoSlide.vue')),
        schedule: defineAsyncComponent(() => import('./slides/ScheduleSlide.vue')),
    };

    const slides = ref([]);

    const currentIndex = ref(0);
    const currentSlide = computed(() => slides.value[currentIndex.value]);

    onMounted(async () => {
        const response = await fetch('/screen/data');
        const assignments = await response.json();

        slides.value = [
            {
                type: 'schedule',
                data: {
                    duration: 8000,
                    rows: assignments,
                },
            },
        ];
    });

    function goToNextSlide() {
        currentIndex.value = (currentIndex.value + 1) % slides.value.length;
    }
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1000ms ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
