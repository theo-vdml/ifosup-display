<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/CourseController';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';

    defineProps<{
        courses: Course[];
    }>();

    const routes = useResourceRoutes(null, actions);

    const getAvatarUrl = (name: string) => {
        return `https://api.dicebear.com/9.x/initials/svg?seed=${encodeURIComponent(name)}`;
    };
</script>

<template>
    <ResourceIndexLayout type="Cours" :routes="routes" :isEmpty="courses.length === 0">
        <template #empty-title>Il n'y a aucun cours.</template>
        <template #empty-action>Créer un cours</template>
        <template #create-action>Créer un cours</template>

        <div class="grid gap-3">
            <ResourceListItem v-for="course in courses" :key="course.id" :href="actions.show(course.id).url"
                              :title="course.name" :image="getAvatarUrl(course.code)">
            </ResourceListItem>
        </div>
    </ResourceIndexLayout>
</template>
