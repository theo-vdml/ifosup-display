<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/CourseController';
    import BaseCombobox from '@/components/BaseCombobox.vue';
    import InputError from '@/components/InputError.vue';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';
    import ResourceFormLayout from '@/layouts/resources/ResourceFormLayout.vue';
    import { ref } from 'vue';

    const routes = useResourceRoutes(null, actions);

    const options = [
        { id: 1, name: 'Option 1' },
        { id: 2, name: 'Option 2' },
        { id: 3, name: 'Option 3' },
    ];

    const selectedOption = ref(null);
</script>

<template>


    <ResourceFormLayout title="Nouveau Cours" description="Ajoutez un nouveau cours dans la base de données."
        type="Cours" :routes="routes" :form-action="actions.store.form()">
        <template #default="{ errors }">
            <BaseCombobox v-model="selectedOption" placeholder="Code du cours" :options="options" :display-value="(option) => option.name" />

            <div class="grid gap-2">
                <Label for="code">Code du cours</Label>
                <Input id="code" class="mt-1 block w-full" name="code" required autocomplete="code"
                    placeholder="Code du cours" />
                <InputError class="mt-2" :message="errors.code" />
            </div>
            <div class="grid gap-2">
                <Label for="name">Nom du cours</Label>
                <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                    placeholder="Nom du cours" />
                <InputError class="mt-2" :message="errors.name" />
            </div>
        </template>
    </ResourceFormLayout>

</template>
