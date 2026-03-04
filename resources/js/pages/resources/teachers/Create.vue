<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/TeacherController';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import InputError from '@/components/InputError.vue';
    import ResourceFormLayout from '@/layouts/resources/ResourceFormLayout.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';

    const routes = useResourceRoutes(null, actions);
</script>

<template>


    <ResourceFormLayout title="Nouvel Enseignant" description="Ajoutez un nouvel enseignant dans la base de données."
        type="Enseignants" :routes="routes" :form-action="actions.store.form()">
        <template #default="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="name">Nom de l'enseignant</Label>
                <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                    placeholder="Nom complet" />
                <InputError class="mt-2" :message="errors.name" />
            </div>
        </template>
    </ResourceFormLayout>

</template>
