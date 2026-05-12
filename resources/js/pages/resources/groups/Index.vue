<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/GroupController'
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';
    import ResourceListItemData from '@/components/resources/ResourceListItemData.vue';
    import { Input } from '@/components/ui/input';
    import { Search } from 'lucide-vue-next';
    import { ref, computed } from 'vue';

    const props = defineProps<{
        groups: Group[];
    }>();

    const routes = useResourceRoutes(null, actions);
    const query = ref('');

    const filteredGroups = computed(() => {
        const q = query.value.trim().toLowerCase();
        if (!q) return props.groups;
        return props.groups.filter((g) => g.name.toLowerCase().includes(q));
    });

    const getAvatarUrl = (name: string) => {
        return `https://api.dicebear.com/9.x/glass/svg?seed=${encodeURIComponent(name)}`;
    };
</script>

<template>
    <ResourceIndexLayout type="Sections" :routes="routes" :isEmpty="groups.length === 0">
        <template #empty-title>Il n'y a aucune section.</template>
        <template #empty-action>Créer une section</template>
        <template #create-action>Créer une section</template>

        <div class="relative mb-4 max-w-sm">
            <Search class="text-muted-foreground pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2" />
            <Input v-model="query" placeholder="Rechercher une section…" class="pl-9" />
        </div>
        <div class="grid gap-3">
            <ResourceListItem v-for="group in filteredGroups" :key="group.id" :href="actions.show(group.id).url"
                :title="group.name" :image="getAvatarUrl(group.name)">
            </ResourceListItem>
            <p v-if="filteredGroups.length === 0 && query" class="text-muted-foreground py-6 text-center text-sm">
                Aucun résultat pour « {{ query }} ».
            </p>
        </div>
    </ResourceIndexLayout>
</template>
