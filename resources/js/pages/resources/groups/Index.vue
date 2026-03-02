<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { show, index, create } from '@/actions/App/Http/Controllers/GroupController'
    import Heading from '@/components/Heading.vue';
    import { Link } from '@inertiajs/vue3';
    import { BreadcrumbItem } from '@/types';

    const props = defineProps<{
        groups: Group[];
    }>();

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Ressources',
            href: '#',
        },
        {
            title: 'Groupes',
            href: index(),
        }
    ];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <div class="flex items-start justify-between">
                    <Heading title="Groupes" description="Gérez la liste des groupes d'étudiants." />
                    <Link :href="create()"
                        class="px-4 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                        Nouveau groupe
                    </Link>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="group in groups" :key="group.id" class="group">
                            <Link :href="show(group.id).url"
                                class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-600 border border-gray-200">
                                        {{ group.name.charAt(0) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ group.name }}</span>
                                </div>

                                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </li>

                        <li v-if="groups.length === 0" class="px-6 py-10 text-center text-sm text-gray-500">
                            Aucun groupe trouvé.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
