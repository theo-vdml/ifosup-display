<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import TeacherController from '@/actions/App/Http/Controllers/TeacherController';
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
            title: 'Enseignants',
            href: TeacherController.index(),
        },
        {
            title: 'Nouveau',
            href: TeacherController.create(),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading title="Nouvel enseignant"
                    description="Ajoutez un nouvel enseignant dans la base de données." />
                <Form v-bind="TeacherController.store.form()" v-slot="{ errors, processing }" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nom de l'enseignant</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                            placeholder="Nom complet" />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Enregistrer</Button>
                    </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
