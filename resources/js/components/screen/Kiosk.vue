<template>
    <div class="h-screen w-screen bg-black overflow-hidden relative">

        <transition name="slide" mode="out-in">
            <component v-if="currentSlide" :is="components[currentSlide.type]" :key="currentIndex"
                :data="currentSlide.data" @next="goToNextSlide" />
        </transition>
    </div>
</template>

<script setup>
    import { ref, computed, defineAsyncComponent, onMounted } from 'vue';

    const components = {
        welcome: defineAsyncComponent(() => import('./slides/Welcome.vue')),
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
                type: 'welcome',
                data: {
                    duration: 5000,
                },
            },
            {
                type: 'schedule',
                data: {
                    duration: 8000,
                    rows: assignments,
                },
            },
            {
                type: 'image',
                data: {
                    duration: 5000,
                    src: 'https://picsum.photos/1080/800',
                },
            }
        ];
    });

    function goToNextSlide() {
        currentIndex.value = (currentIndex.value + 1) % slides.value.length;
    }
</script>

<style scoped>
.slide-enter-active {
    transition: opacity 800ms ease, transform 800ms cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-leave-active {
    transition: opacity 400ms ease, transform 400ms ease;
    position: absolute;
    inset: 0;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(60px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-60px);
}
</style>
