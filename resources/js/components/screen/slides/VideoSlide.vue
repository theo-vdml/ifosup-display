<template>
    <div class="w-full h-full bg-black">
        <video ref="videoPlayer" class="w-full h-full object-cover" autoplay muted playsinline :src="data.src"
            @ended="emit('next')"></video>
    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue';

    defineProps({ data: Object });
    const emit = defineEmits(['next']);

    const videoPlayer = ref(null);

    onMounted(() => {
        if (videoPlayer.value) {
            videoPlayer.value.currentTime = 0;
            videoPlayer.value.play();
        }
    });
</script>
