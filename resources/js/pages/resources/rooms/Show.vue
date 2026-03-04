<script setup lang="ts">
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    import actions from '@/actions/App/Http/Controllers/RoomController';
    import ResourceShowLayout from '@/layouts/resources/ResourceShowLayout.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';

    const props = defineProps<{
        room: Room;
    }>();

    const routes = useResourceRoutes(props.room.id, actions);

</script>

<template>
    <ResourceShowLayout :title="room.name" description="Consultez les détails du local et gérez ses paramètres."
        type="Locaux" :routes="routes">
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern />
        </div>
    </ResourceShowLayout>
</template>
