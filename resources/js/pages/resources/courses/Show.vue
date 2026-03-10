<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/CourseController';
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceShowLayout from '@/layouts/resources/ResourceShowLayout.vue';

    const props = defineProps<{
        course: Course;
    }>();

    const routes = useResourceRoutes(props.course.id, actions);
</script>

<template>
    <ResourceShowLayout
        :title="course.name"
        description="Consultez les détails du cours et gérez ses paramètres."
        type="Cours"
        :routes="routes"
    >
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <PlaceholderPattern />
        </div>
    </ResourceShowLayout>
</template>
