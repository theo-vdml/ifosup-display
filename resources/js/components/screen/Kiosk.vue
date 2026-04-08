<template>
    <div class="h-screen w-screen bg-black overflow-hidden relative">

        <transition name="fade" mode="out-in">
            <component :is="components[currentSlide.type]" :key="currentIndex" :data="currentSlide.data" />
        </transition>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, onUnmounted, defineAsyncComponent } from 'vue';

    const components = {
        image: defineAsyncComponent(() => import('./slides/ImageSlide.vue')),
        video: defineAsyncComponent(() => import('./slides/VideoSlide.vue')),
    };

    const slides = ref([
        { type: 'image', data: { src: 'https://picsum.photos/1920/1080?sig=1' } },
        { type: 'image', data: { src: 'https://picsum.photos/1920/1080?sig=5' } },
        { type: 'video', data: { src: 'https://www.w3schools.com/html/mov_bbb.mp4' } },
    ]);

    const currentIndex = ref(0);
    const currentSlide = computed(() => slides.value[currentIndex.value]);

    let timer = null;

    onMounted(() => {
        timer = setInterval(() => {
            currentIndex.value = (currentIndex.value + 1) % slides.value.length;
        }, 10000);
    });

    onUnmounted(() => clearInterval(timer));
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
