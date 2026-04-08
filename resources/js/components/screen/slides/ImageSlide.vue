<template>
    <div class="w-full h-full">
        <img :src="data.src" class="w-full h-full object-cover" alt="Slide" />
    </div>
</template>

<script setup>
    defineProps({ data: Object });
</script>
