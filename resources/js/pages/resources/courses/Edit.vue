<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/CourseController';
    import InputError from '@/components/InputError.vue';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceFormLayout from '@/layouts/resources/ResourceFormLayout.vue';

    const props = defineProps<{
        course: Course;
    }>();

    const routes = useResourceRoutes(null, actions);
</script>

<template>


    <ResourceFormLayout :title="course.name" description="Modifiez les informations du cours." type="Cours"
                        :routes="routes" :form-action="actions.update.form(props.course.id)" :is-edit="true">
        <template #default="{ errors }">
            <div class="grid gap-2">
                <Label for="name">Nom du cours</Label>
                <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                       placeholder="Nom complet" :default-value="course.name" />
                <InputError class="mt-2" :message="errors.name" />
            </div>
        </template>
    </ResourceFormLayout>

</template>
