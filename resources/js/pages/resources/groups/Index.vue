<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/GroupController'
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';
    import { UsersRoundIcon } from 'lucide-vue-next';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';
    import ResourceListItemData from '@/components/resources/ResourceListItemData.vue';

    const props = defineProps<{
        groups: Group[];
    }>();

    const routes = useResourceRoutes(null, actions)

    const getAvatarUrl = (name: string) => {
        return `https://api.dicebear.com/9.x/glass/svg?seed=${encodeURIComponent(name)}`;
    };
</script>

<template>
    <ResourceIndexLayout type="Groupes" :routes="routes" :isEmpty="groups.length === 0">
        <template #empty-title>Il n'y a aucun groupe.</template>
        <template #empty-action>Créer un groupe</template>
        <template #create-action>Créer un groupe</template>

        <div class="grid gap-3">
            <ResourceListItem v-for="group in groups" :key="group.id" :href="actions.show(group.id).url"
                :title="group.name" :image="getAvatarUrl(group.name)">
            </ResourceListItem>
        </div>
    </ResourceIndexLayout>
</template>
