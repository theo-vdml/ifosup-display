<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { index, store, create } from '@/actions/App/Http/Controllers/GroupController';
    import { Form } from '@inertiajs/vue3';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import InputError from '@/components/InputError.vue';
    import { BreadcrumbItem } from '@/types';
    import Heading from '@/components/Heading.vue';

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
            title: 'Nouveau',
            href: create(),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading title="Nouveau groupe" description="Ajoutez un nouveau groupe dans la base de données." />
                <Form v-bind="store.form()" v-slot="{ errors, processing }" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nom du groupe</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                            placeholder="Nom complet" />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="size">Nombre d'étudiants</Label>
                        <Input id="size" class="mt-1 block w-full" name="size" required type="number" min="1"
                            placeholder="Nombre d'étudiants" />
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
