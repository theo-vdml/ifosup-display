<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import TeacherController from '@/actions/App/Http/Controllers/TeacherController';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import InputError from '@/components/InputError.vue';
    import { Form } from '@inertiajs/vue3';
    import { BreadcrumbItem } from '@/types';
    import teachers from '@/routes/teachers';
    import Heading from '@/components/Heading.vue';

    const props = defineProps<{
        teacher: Teacher;
    }>();

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Ressources',
            href: '#',
        },
        {
            title: 'Enseignants',
            href: teachers.index(),
        },
        {
            title: props.teacher.name,
            href: teachers.show(props.teacher.id),
        },
        {
            title: 'Modifier',
            href: teachers.edit(props.teacher.id),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading :title="teacher.name" description="Modifiez les informations de l'enseignant." />
                <Form v-bind="TeacherController.update.form(props.teacher)" v-slot="{ errors, processing }"
                    class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nom de l'enseignant</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" required autocomplete="name"
                            placeholder="Nom complet" :default-value="teacher.name" />
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
