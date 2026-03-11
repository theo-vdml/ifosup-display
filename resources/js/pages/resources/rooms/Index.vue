<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/RoomController'
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';

    const props = defineProps<{
        rooms: Room[];
    }>();

    const routes = useResourceRoutes(null, actions)

    const getAvatarUrl = (name: string) => {
        return `https://api.dicebear.com/9.x/shapes/svg?seed=${encodeURIComponent(name)}`;
    };
</script>

<template>
    <ResourceIndexLayout type="Locaux" :routes="routes" :isEmpty="rooms.length === 0">
        <template #empty-title>Il n'y a aucun local.</template>
        <template #empty-action>Créer un local</template>
        <template #create-action>Créer un local</template>

        <div class="grid gap-3">
            <ResourceListItem v-for="room in rooms" :key="room.id" :href="actions.show(room.id).url" :title="room.name"
                :image="getAvatarUrl(room.name)">
            </ResourceListItem>
        </div>
    </ResourceIndexLayout>
</template>
