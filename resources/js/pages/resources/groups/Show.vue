<script setup lang="ts">
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    import actions from '@/actions/App/Http/Controllers/GroupController';
    import ResourceShowLayout from '@/layouts/resources/ResourceShowLayout.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';

    const props = defineProps<{
        group: Group;
    }>();

    const routes = useResourceRoutes(props.group.id, actions);

</script>

<template>
    <ResourceShowLayout :title="group.name" description="Consultez les détails du groupe et gérez ses paramètres."
        type="Groupes" :routes="routes">
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern />
        </div>
    </ResourceShowLayout>
</template>
