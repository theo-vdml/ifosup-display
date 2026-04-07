<script setup lang="ts">
    import { router } from '@inertiajs/vue3';
    import { computed, reactive, ref } from 'vue';
    import { PencilLine, Plus, Trash2 } from 'lucide-vue-next';

    import Combobox from '@/components/Combobox.vue';
    import SchedulerWeekPicker from '@/components/SchedulerWeekPicker.vue';
    import { Badge } from '@/components/ui/badge';
    import Heading from '@/components/Heading.vue';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
    import AppLayout from '@/layouts/AppLayout.vue';
    import type { BreadcrumbItem } from '@/types';

    type DayKey = 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' | 'sunday';

    type RecurrenceDraft = {
        day: DayKey;
        period: AssignmentPeriod;
        startWeek: string;
        endWeek: string;
        room: Room | null;
        course: Course | null;
    };

    type RecurrenceRule = {
        id: number;
        day: DayKey;
        period: AssignmentPeriod;
        startWeek: string;
        endWeek: string;
        room: Room;
        course: Course;
    };

    const props = defineProps<{
        rooms: Room[];
        courses: Course[];
        recurringAssignments: RecurringAssignmentWithRelations[];
    }>();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Recurrences',
            href: '/recurrences',
        },
    ];

    const days: Array<{ key: DayKey; label: string; jsDay: number }> = [
        { key: 'monday', label: 'Lundi', jsDay: 1 },
        { key: 'tuesday', label: 'Mardi', jsDay: 2 },
        { key: 'wednesday', label: 'Mercredi', jsDay: 3 },
        { key: 'thursday', label: 'Jeudi', jsDay: 4 },
        { key: 'friday', label: 'Vendredi', jsDay: 5 },
        { key: 'saturday', label: 'Samedi', jsDay: 6 },
        { key: 'sunday', label: 'Dimanche', jsDay: 0 },
    ];

    const periods: Array<{ key: AssignmentPeriod; label: string }> = [
        { key: 'morning', label: 'Matin' },
        { key: 'afternoon', label: 'Apres-midi' },
        { key: 'evening', label: 'Soir' },
    ];

    const FRENCH_DATE_FORMATTER = new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'UTC',
    });

    const toIsoUtcDate = (isoDate: string) => new Date(`${isoDate}T00:00:00Z`);
    const toIsoDate = (date: Date) => date.toISOString().slice(0, 10);

    const weekRegex = /^(\d{4})-W(0[1-9]|[1-4]\d|5[0-3])$/;

    const parseIsoWeek = (value: unknown) => {
        if (typeof value !== 'string') {
            return null;
        }

        const match = value.match(weekRegex);

        if (!match) {
            return null;
        }

        return {
            year: Number(match[1]),
            week: Number(match[2]),
        };
    };

    const isoWeekToMondayDate = (isoWeek: string) => {
        const parsed = parseIsoWeek(isoWeek);

        if (!parsed) {
            return null;
        }

        const jan4 = new Date(Date.UTC(parsed.year, 0, 4));
        const jan4IsoDay = jan4.getUTCDay() || 7;
        const weekOneMonday = new Date(jan4);
        weekOneMonday.setUTCDate(jan4.getUTCDate() - (jan4IsoDay - 1));

        const monday = new Date(weekOneMonday);
        monday.setUTCDate(weekOneMonday.getUTCDate() + (parsed.week - 1) * 7);

        return monday;
    };

    const isoWeekFromDate = (date: Date) => {
        const normalized = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate()));
        const day = normalized.getUTCDay() || 7;
        normalized.setUTCDate(normalized.getUTCDate() + 4 - day);

        const isoYear = normalized.getUTCFullYear();
        const yearStart = new Date(Date.UTC(isoYear, 0, 1));
        const week = Math.ceil((((normalized.getTime() - yearStart.getTime()) / 86400000) + 1) / 7);

        return `${isoYear}-W${String(week).padStart(2, '0')}`;
    };

    const dayLabel = (day: DayKey) => {
        return days.find((item) => item.key === day)?.label ?? day;
    };

    const periodLabel = (period: AssignmentPeriod) => {
        return periods.find((item) => item.key === period)?.label ?? period;
    };

    const displayRoomLabel = (room: Room) => room.name;
    const displayCourseLabel = (course: Course) => `${course.code} - ${course.name}`;

    const formatFrenchDate = (isoDate: string) => {
        return FRENCH_DATE_FORMATTER.format(toIsoUtcDate(isoDate));
    };

    const addWeeks = (isoWeek: string, weeksToAdd: number) => {
        const monday = isoWeekToMondayDate(isoWeek);

        if (!monday) {
            return isoWeek;
        }

        monday.setUTCDate(monday.getUTCDate() + (weeksToAdd * 7));
        return isoWeekFromDate(monday);
    };

    const createDraft = (): RecurrenceDraft => {
        const startWeek = isoWeekFromDate(new Date());

        return {
            day: 'monday',
            period: 'morning',
            startWeek,
            endWeek: addWeeks(startWeek, 16),
            room: props.rooms[0] ?? null,
            course: props.courses[0] ?? null,
        };
    };

    const draft = reactive<RecurrenceDraft>(createDraft());
    const drawerOpen = ref(false);
    const editingId = ref<number | null>(null);
    const errorMessage = ref('');
    const searchQuery = ref('');
    const selectedDay = ref<'all' | DayKey>('all');
    const selectedPeriod = ref<'all' | AssignmentPeriod>('all');

    const isEditing = computed(() => editingId.value !== null);
    const hasCatalogData = computed(() => props.rooms.length > 0 && props.courses.length > 0);

    const dayNumberToKey: Record<number, DayKey> = {
        1: 'monday',
        2: 'tuesday',
        3: 'wednesday',
        4: 'thursday',
        5: 'friday',
        6: 'saturday',
        7: 'sunday',
    };

    const dayKeyToNumber: Record<DayKey, number> = {
        monday: 1,
        tuesday: 2,
        wednesday: 3,
        thursday: 4,
        friday: 5,
        saturday: 6,
        sunday: 7,
    };

    const rules = computed<RecurrenceRule[]>(() => {
        return props.recurringAssignments
            .filter((item) => item.course && item.room && parseIsoWeek(item.start_week) && parseIsoWeek(item.end_week))
            .map((item) => ({
                id: item.id,
                day: dayNumberToKey[item.day_of_week] ?? 'monday',
                period: item.period,
                startWeek: item.start_week as string,
                endWeek: item.end_week as string,
                room: item.room,
                course: item.course,
            }));
    });

    const resetDraft = () => {
        Object.assign(draft, createDraft());
        editingId.value = null;
        errorMessage.value = '';
    };

    const openCreateDrawer = () => {
        resetDraft();
        drawerOpen.value = true;
    };

    const openEditDrawer = (rule: RecurrenceRule) => {
        editingId.value = rule.id;
        errorMessage.value = '';
        draft.day = rule.day;
        draft.period = rule.period;
        draft.startWeek = rule.startWeek;
        draft.endWeek = rule.endWeek;
        draft.room = props.rooms.find((room) => room.id === rule.room.id) ?? rule.room;
        draft.course = props.courses.find((course) => course.id === rule.course.id) ?? rule.course;
        drawerOpen.value = true;
    };

    const onStartWeekChange = (value: string) => {
        draft.startWeek = value;

        if (draft.endWeek < draft.startWeek) {
            draft.endWeek = draft.startWeek;
        }
    };

    const onEndWeekChange = (value: string) => {
        draft.endWeek = value;

        if (draft.startWeek > draft.endWeek) {
            draft.startWeek = draft.endWeek;
        }
    };

    const deleteRule = (id: number) => {
        router.delete(`/recurrences/${id}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const validateDraft = () => {
        if (!hasCatalogData.value) {
            errorMessage.value = 'Ajoutez au moins un local et un cours pour creer des recurrences.';
            return false;
        }

        if (!draft.startWeek || !draft.endWeek) {
            errorMessage.value = 'Les dates de debut et de fin sont obligatoires.';
            return false;
        }

        if (!weekRegex.test(draft.startWeek) || !weekRegex.test(draft.endWeek)) {
            errorMessage.value = 'Le format de semaine doit etre du type 2026-W13.';
            return false;
        }

        if (draft.startWeek > draft.endWeek) {
            errorMessage.value = 'La date de debut doit etre avant la date de fin.';
            return false;
        }

        if (!draft.room || !draft.course) {
            errorMessage.value = 'Selectionnez un local et un cours.';
            return false;
        }

        errorMessage.value = '';
        return true;
    };

    const saveRule = () => {
        if (!validateDraft()) {
            return;
        }

        const payload = {
            day_of_week: dayKeyToNumber[draft.day],
            period: draft.period,
            start_week: draft.startWeek,
            end_week: draft.endWeek,
            room_id: (draft.room as Room).id,
            course_id: (draft.course as Course).id,
        };

        if (isEditing.value) {
            router.patch(`/recurrences/${editingId.value}`, payload, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    drawerOpen.value = false;
                    resetDraft();
                },
            });
            return;
        }

        router.post('/recurrences', payload, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                drawerOpen.value = false;
                resetDraft();
            },
        });
    };

    const normalize = (value: string) => {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('fr')
            .trim();
    };

    const compareRulesByStartDate = (a: RecurrenceRule, b: RecurrenceRule) => {
        if (a.startWeek !== b.startWeek) {
            return a.startWeek.localeCompare(b.startWeek);
        }

        if (a.endWeek !== b.endWeek) {
            return a.endWeek.localeCompare(b.endWeek);
        }

        return a.course.code.localeCompare(b.course.code, 'fr');
    };

    const filteredRules = computed(() => {
        const query = normalize(searchQuery.value);

        if (!query) {
            return rules.value;
        }

        return rules.value.filter((rule) => {
            const range = getRangeBoundaryDates(rule);

            const haystack = normalize([
                dayLabel(rule.day),
                periodLabel(rule.period),
                rule.course.code,
                rule.course.name,
                rule.room.name,
                rule.startWeek,
                rule.endWeek,
                `${formatFrenchDate(range.from)} ${formatFrenchDate(range.to)}`,
            ].join(' '));

            return haystack.includes(query);
        });
    });

    const dayCounters = computed(() => {
        const counters = new Map<DayKey, number>();

        for (const day of days) {
            counters.set(day.key, 0);
        }

        for (const rule of filteredRules.value) {
            counters.set(rule.day, (counters.get(rule.day) ?? 0) + 1);
        }

        return counters;
    });

    const selectedDayRules = computed(() => {
        if (selectedDay.value === 'all') {
            return filteredRules.value;
        }

        return filteredRules.value.filter((rule) => rule.day === selectedDay.value);
    });

    const visibleRules = computed(() => {
        const scoped = selectedDayRules.value.filter((rule) => {
            return selectedPeriod.value === 'all' || rule.period === selectedPeriod.value;
        });

        return scoped.sort(compareRulesByStartDate);
    });

    const periodCounters = computed(() => {
        const counters: Record<'all' | AssignmentPeriod, number> = {
            all: selectedDayRules.value.length,
            morning: 0,
            afternoon: 0,
            evening: 0,
        };

        for (const rule of selectedDayRules.value) {
            counters[rule.period] += 1;
        }

        return counters;
    });

    const getRangeBoundaryDates = (rule: RecurrenceRule) => {
        const fromMonday = isoWeekToMondayDate(rule.startWeek);
        const toMonday = isoWeekToMondayDate(rule.endWeek);

        if (!fromMonday || !toMonday) {
            const fallback = toIsoDate(new Date());
            return { from: fallback, to: fallback };
        }

        const toSunday = new Date(toMonday);
        toSunday.setUTCDate(toSunday.getUTCDate() + 6);

        return {
            from: toIsoDate(fromMonday),
            to: toIsoDate(toSunday),
        };
    };

    const countDayOccurrences = (rule: RecurrenceRule) => {
        const startMonday = isoWeekToMondayDate(rule.startWeek);
        const endMonday = isoWeekToMondayDate(rule.endWeek);

        if (!startMonday || !endMonday || startMonday > endMonday) {
            return 0;
        }

        const diffInDays = Math.floor((endMonday.getTime() - startMonday.getTime()) / 86400000);
        return Math.floor(diffInDays / 7) + 1;
    };

    const formatRuleRange = (rule: RecurrenceRule) => {
        const boundaries = getRangeBoundaryDates(rule);

        return {
            from: formatFrenchDate(boundaries.from),
            to: formatFrenchDate(boundaries.to),
            startWeek: rule.startWeek,
            endWeek: rule.endWeek,
        };
    };

    const formatRuleOccurrencesLabel = (rule: RecurrenceRule) => {
        const occurrences = countDayOccurrences(rule);
        return `${occurrences} occurrence(s) ${dayLabel(rule.day).toLocaleLowerCase('fr')}`;
    };

    const setSelectedDay = (day: 'all' | DayKey) => {
        selectedDay.value = day;
    };

    const getDayCount = (day: 'all' | DayKey) => {
        if (day === 'all') {
            return filteredRules.value.length;
        }

        return dayCounters.value.get(day) ?? 0;
    };
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 w-full max-w-5xl mx-auto">
            <div class="flex flex-col space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <Heading title="Recurrences"
                        description="Definissez des regles hebdomadaires avec periode, dates, local et cours." />
                    <Button class="gap-1" :disabled="!hasCatalogData" @click="openCreateDrawer">
                        <Plus class="size-4" />
                        Creer une recurrence
                    </Button>
                </div>

                <div v-if="!hasCatalogData"
                    class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Impossible de creer une recurrence: la base doit contenir au moins un local et un cours.
                </div>

                <div v-if="!rules.length"
                    class="aspect-video w-full rounded-xl border-dashed border-secondary border-2">
                    <div class="flex flex-col items-center justify-center gap-3 w-full h-full">
                        <p class="font-semibold text-lg">Aucune recurrence</p>
                        <p class="text-muted-foreground text-sm max-w-xs text-center">
                            Commencez par creer une regle pour l afficher ici.
                        </p>
                        <Button :disabled="!hasCatalogData" @click="openCreateDrawer">Creer une recurrence</Button>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div class="rounded-xl border border-sidebar-border/60 bg-white dark:bg-zinc-900/50 p-3 sm:p-4">
                        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                            <Input v-model="searchQuery" placeholder="Rechercher cours, local, periode ou date..." />

                            <p class="text-xs text-muted-foreground lg:text-right">
                                {{ visibleRules.length }} regle(s) affichee(s)
                                <span class="hidden sm:inline"> · {{ filteredRules.length }} correspondance(s)
                                    globale(s)</span>
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-sidebar-border/60 bg-white dark:bg-zinc-900/50 overflow-hidden">
                        <div class="border-b px-3 py-3 sm:px-4">
                            <div class="flex gap-2 overflow-x-auto pb-1">
                                <Button :variant="selectedDay === 'all' ? 'default' : 'outline'" size="sm"
                                    class="shrink-0" @click="setSelectedDay('all')">
                                    Tout
                                    <Badge variant="secondary" class="ml-2 px-1.5 py-0">{{ getDayCount('all') }}</Badge>
                                </Button>

                                <Button v-for="day in days" :key="day.key" size="sm"
                                    :variant="selectedDay === day.key ? 'default' : 'outline'" class="shrink-0"
                                    @click="setSelectedDay(day.key)">
                                    {{ day.label }}
                                    <Badge variant="secondary" class="ml-2 px-1.5 py-0">{{ getDayCount(day.key) }}
                                    </Badge>
                                </Button>
                            </div>
                        </div>

                        <div class="border-b px-3 py-3 sm:px-4">
                            <div class="flex gap-2 overflow-x-auto pb-1">
                                <Button size="sm" :variant="selectedPeriod === 'all' ? 'default' : 'outline'"
                                    class="shrink-0" @click="selectedPeriod = 'all'">
                                    Tout
                                    <Badge variant="secondary" class="ml-2 px-1.5 py-0">{{ periodCounters.all }}</Badge>
                                </Button>
                                <Button v-for="period in periods" :key="period.key" size="sm"
                                    :variant="selectedPeriod === period.key ? 'default' : 'outline'" class="shrink-0"
                                    @click="selectedPeriod = period.key">
                                    {{ period.label }}
                                    <Badge variant="secondary" class="ml-2 px-1.5 py-0">{{ periodCounters[period.key] }}
                                    </Badge>
                                </Button>
                            </div>
                        </div>

                        <div v-if="!visibleRules.length" class="px-4 py-12 text-center text-sm text-muted-foreground">
                            Aucune regle pour ces filtres.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full min-w-225 text-left">
                                <thead class="bg-zinc-50/60 dark:bg-zinc-900/50 border-b">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                            Creneau</th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                            Plage</th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                            Cours</th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                            Local</th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 text-right">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="rule in visibleRules" :key="rule.id"
                                        class="hover:bg-zinc-50/50 dark:hover:bg-zinc-900/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="space-y-0.5">
                                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{
                                                    dayLabel(rule.day) }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{
                                                    periodLabel(rule.period) }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="space-y-0.5 min-w-56">
                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">Du {{
                                                    formatRuleRange(rule).from }}</p>
                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">Au {{
                                                    formatRuleRange(rule).to }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{
                                                    formatRuleOccurrencesLabel(rule) }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="space-y-0.5">
                                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{
                                                    rule.course.code }}</p>
                                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ rule.course.name
                                                    }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{
                                                rule.room.name }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <Button size="icon-sm" variant="outline" @click="openEditDrawer(rule)"
                                                    title="Modifier" aria-label="Modifier">
                                                    <PencilLine class="size-3.5" />
                                                </Button>
                                                <Button size="icon-sm" variant="outline" class="text-red-600"
                                                    @click="deleteRule(rule.id)" title="Supprimer"
                                                    aria-label="Supprimer">
                                                    <Trash2 class="size-3.5" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Sheet v-model:open="drawerOpen">
            <SheetContent side="right" class="w-full sm:max-w-xl p-0">
                <SheetHeader class="border-b border-slate-200 pb-4 bg-zinc-50/70 dark:bg-zinc-900/60">
                    <SheetTitle class="text-base">
                        {{ isEditing ? 'Modifier la recurrence' : 'Nouvelle recurrence' }}
                    </SheetTitle>
                    <SheetDescription>
                        Configurez le jour, la periode, les dates, le local et le cours.
                    </SheetDescription>
                </SheetHeader>

                <div class="space-y-5 px-4 py-4">
                    <div class="grid gap-2">
                        <Label>Jour</Label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <Button v-for="day in days" :key="day.key" type="button"
                                :variant="draft.day === day.key ? 'default' : 'outline'" class="justify-start"
                                @click="draft.day = day.key">
                                {{ day.label }}
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Periode</Label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <Button v-for="period in periods" :key="period.key" type="button"
                                :variant="draft.period === period.key ? 'default' : 'outline'"
                                @click="draft.period = period.key">
                                {{ period.label }}
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label>Semaine de debut</Label>
                            <SchedulerWeekPicker :model-value="draft.startWeek"
                                @update:model-value="onStartWeekChange" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Semaine de fin</Label>
                            <SchedulerWeekPicker :model-value="draft.endWeek" @update:model-value="onEndWeekChange" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Local</Label>
                        <Combobox v-model="draft.room" :options="props.rooms" value-key="id"
                            :display-function="displayRoomLabel" placeholder="Selectionner un local" nullable />
                    </div>

                    <div class="grid gap-2">
                        <Label>Cours</Label>
                        <Combobox v-model="draft.course" :options="props.courses" value-key="id"
                            :display-function="displayCourseLabel" placeholder="Selectionner un cours" nullable />
                    </div>

                    <p v-if="errorMessage"
                        class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ errorMessage }}
                    </p>
                </div>

                <SheetFooter class="border-t border-slate-200">
                    <Button variant="outline" @click="drawerOpen = false">Annuler</Button>
                    <Button @click="saveRule">{{ isEditing ? 'Enregistrer' : 'Ajouter la recurrence' }}</Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
