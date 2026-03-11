<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/TeacherController'
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';

    const props = defineProps<{
        teachers: Teacher[];
    }>();

    const routes = useResourceRoutes(null, actions)

    const getAvatarUrl = (name: string) => {
        return `https://api.dicebear.com/9.x/thumbs/svg?seed=${encodeURIComponent(name)}`;
    };
</script>

<template>
    <ResourceIndexLayout type="Enseignants" :routes="routes" :isEmpty="teachers.length === 0">
        <template #empty-title>Il n'y a aucun enseignant.</template>
        <template #empty-action>Créer un enseignant</template>
        <template #create-action>Créer un enseignant</template>

        <div class="grid gap-3">
            <ResourceListItem v-for="teacher in teachers" :key="teacher.id" :href="actions.show(teacher.id).url"
                :title="teacher.name" :image="getAvatarUrl(teacher.name)">
            </ResourceListItem>
        </div>
    </ResourceIndexLayout>
</template>
