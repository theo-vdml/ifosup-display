<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/Admin/UserController';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';

    const props = defineProps<{
        users: { id: number; name: string; email: string; created_at: string }[];
    }>();

    const routes = useResourceRoutes(null, actions);
</script>

<template>
    <ResourceIndexLayout type="Utilisateurs" description="Gérez les comptes utilisateurs de l'application."
        :routes="routes" :isEmpty="users.length === 0">
        <template #empty-title>Aucun utilisateur.</template>
        <template #empty-action>Créer un utilisateur</template>
        <template #create-action>Créer un utilisateur</template>

        <div class="grid gap-3">
            <ResourceListItem v-for="user in users" :key="user.id" :href="actions.show(user.id).url" :title="user.name">
                <span class="text-xs text-zinc-400">{{ user.email }}</span>
            </ResourceListItem>
        </div>
    </ResourceIndexLayout>
</template>
