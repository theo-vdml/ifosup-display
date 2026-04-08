<template>
    <div class="w-full h-full">
        <img :src="data.src" class="w-full h-full object-cover" alt="Slide" />
    </div>
</template>

<script setup>
    import { onMounted, onUnmounted } from 'vue';

    const props = defineProps({ data: Object });
    const emit = defineEmits(['next']);

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
