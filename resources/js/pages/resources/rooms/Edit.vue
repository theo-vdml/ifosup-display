<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import RoomController from '@/actions/App/Http/Controllers/RoomController';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import InputError from '@/components/InputError.vue';
    import { Form } from '@inertiajs/vue3';
    import { BreadcrumbItem } from '@/types';
    import rooms from '@/routes/rooms';
    import Heading from '@/components/Heading.vue';

    const props = defineProps<{
        room: Room;
    }>();

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Ressources',
            href: '#',
        },
        {
            title: 'Locaux',
            href: rooms.index(),
        },
        {
            title: props.room.name,
            href: rooms.show(props.room.id),
        },
        {
            title: 'Modifier',
            href: rooms.edit(props.room.id),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading :title="room.name" description="Modifiez les informations du local." />
                <Form v-bind="RoomController.update.form(props.room)" v-slot="{ errors, processing }" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nom du local</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                            placeholder="Nom du local" :default-value="room.name" />
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
