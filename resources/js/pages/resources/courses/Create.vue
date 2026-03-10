<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/CourseController';
    import InputError from '@/components/InputError.vue';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceFormLayout from '@/layouts/resources/ResourceFormLayout.vue';

    const routes = useResourceRoutes(null, actions);
</script>

<template>


    <ResourceFormLayout title="Nouveau Cours" description="Ajoutez un nouveau cours dans la base de données."
                        type="Cours" :routes="routes" :form-action="actions.store.form()">
        <template #default="{ errors }">
            <div class="grid gap-2">
                <Label for="name">Nom du cours</Label>
                <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                       placeholder="Nom du cours" />
                <InputError class="mt-2" :message="errors.name" />
            </div>
        </template>
    </ResourceFormLayout>

</template>
