<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import RoomController from '@/actions/App/Http/Controllers/RoomController';
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
            title: 'Locaux',
            href: RoomController.index(),
        },
        {
            title: 'Nouveau',
            href: RoomController.create(),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading title="Nouveau local" description="Ajoutez un nouveau local dans la base de données." />
                <Form v-bind="RoomController.store.form()" v-slot="{ errors, processing }" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nom du local</Label>
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
