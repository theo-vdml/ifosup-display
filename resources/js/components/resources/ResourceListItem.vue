<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { ChevronRightIcon } from 'lucide-vue-next';
    import { computed } from 'vue';

    interface Props {
        href?: string;
        image?: string;
        title: string;
        actionText?: string;
        showAction?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        showAction: true,
        actionText: 'Voir',
    });

    // Détermine si on rend un Inertia Link ou une simple Div (si pas de href)
    const componentType = computed(() => (props.href ? Link : 'div'));
</script>

<template>
    <component :is="componentType" :href="href"
        class="group relative flex items-center justify-between p-4 rounded-xl border border-sidebar-border/60 bg-white dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-900 hover:border-sidebar-border hover:shadow-md hover:shadow-black/5 transition-all duration-200 no-underline">

        <div class="flex items-center space-x-4">

            <div v-if="image || $slots.image"
                class="relative h-12 w-12 shrink-0 self-start overflow-hidden rounded-lg border border-sidebar-border bg-zinc-100 dark:bg-zinc-800 transition-transform group-hover:scale-105">
                <slot name="image">
                    <img :src="image" :alt="title" class="h-full w-full object-cover" />
                </slot>
            </div>

            <div class="flex flex-col justify-center">
                <span
                    class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-primary transition-colors">
                    {{ title }}
                </span>

                <div class="flex flex-col space-y-0.5 mt-0.5">
                    <slot />
                </div>
            </div>
        </div>

        <div v-if="showAction" class="flex items-center gap-3">
            <span v-if="actionText"
                class="hidden sm:inline-block text-[10px] font-bold uppercase tracking-widest text-zinc-400 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                {{ actionText }}
            </span>
            <div
                class="rounded-full p-1 text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-zinc-100 transition-colors">
                <slot name="action-icon">
                    <ChevronRightIcon class="w-4 h-4 translate-x-0 group-hover:translate-x-0.5 transition-transform" />
                </slot>
            </div>
        </div>
    </component>
</template>
