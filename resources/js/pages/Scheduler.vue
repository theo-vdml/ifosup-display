<script setup lang="ts">
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { scheduler } from '@/routes';
    import { BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Planning',
            href: scheduler(),
        },
    ];

</script>

<template>
    <AppLayout :breadcrumbs>
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
