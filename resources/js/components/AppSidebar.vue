<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { BookOpen, GraduationCap, DoorOpen, UsersRound, CalendarRange, Monitor, ShieldEllipsis, Tv } from 'lucide-vue-next';
    import AppLogo from '@/components/AppLogo.vue';
    import NavFooter from '@/components/NavFooter.vue';
    import NavMain from '@/components/NavMain.vue';
    import NavUser from '@/components/NavUser.vue';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { schedule } from '@/routes';
    import type { NavItem } from '@/types';
    import teachers from '@/routes/teachers';
    import rooms from '@/routes/rooms';
    import groups from '@/routes/groups';
    import courses from '@/routes/courses';

    const resourceNavItems: NavItem[] = [
        {
            title: 'Enseignants',
            href: teachers.index(),
            icon: GraduationCap,
        },
        {
            title: 'Locaux',
            href: rooms.index(),
            icon: DoorOpen,
        },
        {
            title: 'Sections',
            href: groups.index(),
            icon: UsersRound,
        },
        {
            title: 'Cours',
            href: courses.index(),
            icon: BookOpen,
        },
    ];

    const displayNavItems: NavItem[] = [
        {
            title: 'Planning',
            href: schedule(),
            icon: CalendarRange,
        },
        {
            title: 'Slides écran',
            href: '/screen/slides',
            icon: Monitor,
        },
    ];

    const adminNavItems: NavItem[] = [
        {
            title: 'Utilisateurs',
            href: '/admin/users',
            icon: ShieldEllipsis,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Écran TV',
            href: '/screen',
            icon: Tv,
        },
    ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="schedule()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="resourceNavItems" label="Ressources" />
            <NavMain :items="displayNavItems" label="Affichage" />
            <NavMain :items="adminNavItems" label="Administration" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
