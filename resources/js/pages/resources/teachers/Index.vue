<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/TeacherController'
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceIndexLayout from '@/layouts/resources/ResourceIndexLayout.vue';
    import ResourceListItem from '@/components/resources/ResourceListItem.vue';
    import { Input } from '@/components/ui/input';
    import { Search } from 'lucide-vue-next';
    import { ref, computed } from 'vue';

    const props = defineProps<{
        teachers: Teacher[];
    }>();

    const routes = useResourceRoutes(null, actions);
    const query = ref('');

    const filteredTeachers = computed(() => {
        const q = query.value.trim().toLowerCase();
        if (!q) return props.teachers;
        return props.teachers.filter((t) => t.name.toLowerCase().includes(q));
    });

    const getAvatarUrl = (name: string) => {
        return `https://api.dicebear.com/9.x/thumbs/svg?seed=${encodeURIComponent(name)}`;
    };
</script>

<template>
    <ResourceIndexLayout type="Enseignants" :routes="routes" :isEmpty="teachers.length === 0">
        <template #empty-title>Il n'y a aucun enseignant.</template>
        <template #empty-action>Créer un enseignant</template>
        <template #create-action>Créer un enseignant</template>

        <div class="relative mb-4 max-w-sm">
            <Search class="text-muted-foreground pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2" />
            <Input v-model="query" placeholder="Rechercher un enseignant…" class="pl-9" />
        </div>
        <div class="grid gap-3">
            <ResourceListItem v-for="teacher in filteredTeachers" :key="teacher.id" :href="actions.show(teacher.id).url"
                :title="teacher.name" :image="getAvatarUrl(teacher.name)">
            </ResourceListItem>
            <p v-if="filteredTeachers.length === 0 && query" class="text-muted-foreground py-6 text-center text-sm">
                Aucun résultat pour « {{ query }} ».
            </p>
        </div>
    </ResourceIndexLayout>
</template>
