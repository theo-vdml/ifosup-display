<script setup lang="ts">
    import Scheduler from '@/components/Scheduler.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { schedule } from '@/routes';
    import { BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Planning',
            href: schedule(),
        },
    ];

</script>

<template>
    <AppLayout :breadcrumbs>
        <div class="h-full p-4">
            <Scheduler />
        </div>
    </AppLayout>
</template>
