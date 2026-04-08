<template>
    <div class="h-screen w-screen bg-black overflow-hidden relative">

        <transition name="fade" mode="out-in">
            <component :is="components[currentSlide.type]" :key="currentIndex" :data="currentSlide.data"
                @next="goToNextSlide" />
        </transition>
    </div>
</template>

<script setup>
    import { ref, computed, defineAsyncComponent } from 'vue';

    const components = {
        image: defineAsyncComponent(() => import('./slides/ImageSlide.vue')),
        video: defineAsyncComponent(() => import('./slides/VideoSlide.vue')),
    };

    const slides = ref([
        { type: 'image', data: { src: 'https://picsum.photos/1920/1080?sig=1', duration: 3000 } },
        { type: 'image', data: { src: 'https://picsum.photos/1920/1080?sig=5', duration: 5000 } },
        { type: 'video', data: { src: 'https://lorem.video/1280x720' } },
    ]);

    const currentIndex = ref(0);
    const currentSlide = computed(() => slides.value[currentIndex.value]);

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
