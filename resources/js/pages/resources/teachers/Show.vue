<script setup lang="ts">
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    import actions from '@/actions/App/Http/Controllers/TeacherController';
    import ResourceShowLayout from '@/layouts/resources/ResourceShowLayout.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';

    const props = defineProps<{
        teacher: Teacher;
    }>();

    const routes = useResourceRoutes(props.teacher.id, actions);

</script>

<template>
    <ResourceShowLayout :title="teacher.name"
        description="Consultez les détails de l'enseignant et gérez ses paramètres." type="Enseignants"
        :routes="routes">
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern />
        </div>
    </ResourceShowLayout>
</template>
