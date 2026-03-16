<script setup lang="ts">
    import Scheduler from '@/components/Scheduler.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { schedule } from '@/routes';
    import { BreadcrumbItem } from '@/types';

    const props = defineProps<{
        rooms: Room[]
        assignments: Assignment[]
    }>();

    console.log('Schedule props:', props);

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
            <Scheduler :rooms :assignments />
        </div>
    </AppLayout>
</template>
