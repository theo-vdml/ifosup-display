<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { index, show, edit, update } from '@/actions/App/Http/Controllers/GroupController';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import InputError from '@/components/InputError.vue';
    import { Form } from '@inertiajs/vue3';
    import { BreadcrumbItem } from '@/types';
    import Heading from '@/components/Heading.vue';

    const props = defineProps<{
        group: Group;
    }>();

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Ressources',
            href: '#',
        },
        {
            title: 'Groupes',
            href: index(),
        },
        {
            title: props.group.name,
            href: show(props.group.id),
        },
        {
            title: 'Modifier',
            href: edit(props.group.id),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading :title="group.name" description="Modifiez les informations du groupe." />
                <Form v-bind="update.form(props.group)" v-slot="{ errors, processing }" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nom du groupe</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                            placeholder="Nom du groupe" :default-value="group.name" />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="size">Nombre d'étudiants</Label>
                        <Input id="size" class="mt-1 block w-full" name="size" required type="number" min="1"
                            placeholder="Nombre d'étudiants" :default-value="group.size" />
                        <InputError class="mt-2" :message="errors.size" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Enregistrer</Button>
                    </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
