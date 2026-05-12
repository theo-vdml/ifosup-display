<script setup lang="ts">
    import { Head, Link, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import { login, schedule } from '@/routes';

    const page = usePage();
    const isLoggedIn = computed(() => !!(page.props.auth as any)?.user);
    const destination = computed(() => isLoggedIn.value ? schedule.url() : login().url);
</script>

<template>

    <Head title="Bienvenue" />
    <div class="relative min-h-screen overflow-hidden flex flex-col" style="background-color: #1e2d55;">

        <!-- Background decoration -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-40 -right-40 w-150 h-150 rounded-full opacity-10"
                style="background: radial-gradient(circle, #f2ae35, transparent 70%);" />
            <div class="absolute -bottom-32 -left-32 w-100 h-100 rounded-full opacity-[0.07]"
                style="background: radial-gradient(circle, #f2ae35, transparent 70%);" />
        </div>

        <!-- Header -->
        <header class="relative z-10 flex items-center justify-between px-10 py-6 border-b"
            style="border-color: rgba(242,174,53,0.15);">
            <div class="flex items-center gap-4">
                <img src="/IFO_Gimmick_SUPERIEUR.png" alt="IFOSUP" class="h-10 w-auto object-contain" />
                <span class="text-base font-black uppercase tracking-[0.2em]" style="color: #f2ae35;">IFOSUP
                    Display</span>
            </div>
            <div class="flex items-center gap-6">
                <Link href="/screen"
                    class="text-sm font-bold uppercase tracking-wider transition-all duration-200 hover:opacity-80"
                    style="color: rgba(242,174,53,0.6);">
                    Écran TV
                </Link>
                <Link :href="destination"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded text-sm font-bold uppercase tracking-wider border-2 transition-all duration-200 hover:opacity-80"
                    style="border-color: #f2ae35; color: #f2ae35;">
                    {{ isLoggedIn ? 'Ouvrir' : 'Se connecter' }}
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 py-24 text-center">
            <div class="max-w-2xl w-full flex flex-col items-center gap-10">

                <!-- Badge -->
                <span
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border"
                    style="border-color: rgba(242,174,53,0.4); color: #f2ae35; background-color: rgba(242,174,53,0.08);">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse inline-block"
                        style="background-color: #f2ae35;" />
                    Outil interne IFOSUP
                </span>

                <!-- Headline -->
                <div class="flex flex-col gap-5">
                    <h1 class="text-6xl font-black uppercase leading-none tracking-tight text-white">
                        Gestion de<br>
                        <span style="color: #f2ae35;">l'affichage</span>
                    </h1>
                    <p class="text-lg leading-relaxed max-w-lg mx-auto" style="color: rgba(255,255,255,0.6);">
                        Pilotez en temps réel le contenu diffusé sur les écrans de l'établissement &mdash; plannings,
                        slides, annonces.
                    </p>
                </div>

                <!-- CTA -->
                <Link :href="destination"
                    class="inline-flex items-center gap-3 px-8 py-4 rounded-lg text-base font-black uppercase tracking-widest transition-all duration-200 hover:brightness-110 shadow-lg"
                    style="background-color: #f2ae35; color: #1e2d55;">
                    Accéder à la plateforme
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </Link>

            </div>
        </main>

    </div>
</template>
