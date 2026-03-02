<script setup lang="ts">
    import Heading from '@/components/Heading.vue';
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { index, show, edit, destroy } from '@/actions/App/Http/Controllers/GroupController';
    import { BreadcrumbItem } from '@/types';
    import { Link, router } from '@inertiajs/vue3';

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
        }
    ];

    const deleteGroup = () => {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Cette action est irréversible.')) {
            // Remplace par ton helper de route si tu en as un, sinon l'URL brute
            router.delete(destroy(props.group.id).url);
        }
    };

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <div class="flex items-start justify-between">
                    <Heading :title="group.name" description="Consultez les détails du groupe." />
                    <Link :href="edit(props.group.id).url"
                        class="px-4 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                        Modifier
                    </Link>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="pt-10">
                    <div
                        class="rounded-xl border border-red-200 bg-red-50/30 p-6 dark:border-red-900/50 dark:bg-red-900/10">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col space-y-1">
                                <h3 class="text-sm font-bold text-red-600 dark:text-red-400">Zone de danger</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Supprimer définitivement ce groupe et toutes les données associées.
                                </p>
                            </div>
                            <button @click="deleteGroup"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition shadow-sm cursor-pointer">
                                Supprimer le groupe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
