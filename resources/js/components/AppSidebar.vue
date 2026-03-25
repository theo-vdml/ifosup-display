<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { BookOpen, FolderGit2, LayoutGrid, GraduationCap, DoorOpen, UsersRound, CalendarRange, Repeat2 } from 'lucide-vue-next';
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
    import { dashboard, schedule } from '@/routes';
    import type { NavItem } from '@/types';
    import teachers from '@/routes/teachers';
    import rooms from '@/routes/rooms';
    import groups from '@/routes/groups';
    import courses from '@/routes/courses';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
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
            icon: BookOpen
        },
        {
            title: 'Planning',
            href: schedule(),
            icon: CalendarRange
        },
        {
            title: 'Recurrences',
            href: '/recurrences',
            icon: Repeat2,
        }
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/vue-starter-kit',
            icon: FolderGit2,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#vue',
            icon: BookOpen,
        },
    ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
