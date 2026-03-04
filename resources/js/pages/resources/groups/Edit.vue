<script setup lang="ts">
    import actions from '@/actions/App/Http/Controllers/GroupController';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import InputError from '@/components/InputError.vue';
    import ResourceFormLayout from '@/layouts/resources/ResourceFormLayout.vue';
    import { useResourceRoutes } from '@/composables/useResourceRoutes';

    const props = defineProps<{
        group: Group;
    }>();

    const routes = useResourceRoutes(null, actions);
</script>

<template>


    <ResourceFormLayout :title="group.name" description="Modifiez les informations du groupe." type="Groupes"
        :routes="routes" :form-action="actions.update.form(props.group.id)" :is-edit="true">
        <template #default="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="name">Nom du groupe</Label>
                <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                    placeholder="Nom complet" :default-value="group.name" />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="size">Nombre d'étudiants</Label>
                <Input id="size" class="mt-1 block w-full" name="size" required type="number" min="1"
                    placeholder="Nombre d'étudiants" :default-value="group.size" />
                <InputError class="mt-2" :message="errors.size" />
            </div>
        </template>
    </ResourceFormLayout>

</template>
