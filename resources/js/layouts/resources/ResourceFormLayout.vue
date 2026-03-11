<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Button } from '@/components/ui/button';
    import { Form, Link } from '@inertiajs/vue3';
    import { BreadcrumbItem } from '@/types';
    import Heading from '@/components/Heading.vue';
    import { ResourceRoutes } from '@/composables/useResourceRoutes';
    import { RouteFormDefinition } from '@/wayfinder';

    interface ResourceFormLayoutProps {
        title: string;
        type: string;
        routes: ResourceRoutes;
        formAction: RouteFormDefinition<any>;
        description?: string;
        isEdit?: boolean;
    };

    const props = withDefaults(defineProps<ResourceFormLayoutProps>(), {
        description: 'Remplissez les informations de la ressource.',
        isEdit: false,
    });

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Ressources',
            href: '#',
        },
        {
            title: props.type,
            href: props.routes.index,
        },
        ...(props.isEdit ? [{
            title: props.title,
            href: props.routes.show
        }, {
            title: 'Modifier',
            href: props.routes.edit
        }] : [{
            title: 'Créer',
            href: props.routes.create
        }])
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <Heading :title="props.title" :description="props.description" />
                <Form v-bind="props.formAction" v-slot="{ errors, processing }" class="space-y-6">

                    <slot :errors="errors" :processing="processing" />

                    <div class="flex items-center gap-4">
                        <Button as-child :disabled="processing" variant="destructive">
                            <Link :href="props.isEdit ? props.routes.show : props.routes.index">
                                Annuler
                            </Link>
                        </Button>
                        <Button :disabled="processing">Enregistrer</Button>
                    </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
