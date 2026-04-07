<script setup lang="ts">
import { CalendarDate, type DateValue } from '@internationalized/date';
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
    DateRangeFieldInput,
    DateRangeFieldRoot,
    PopoverContent,
    PopoverPortal,
    PopoverRoot,
    PopoverTrigger,
    RangeCalendarCell,
    RangeCalendarCellTrigger,
    RangeCalendarGrid,
    RangeCalendarGridBody,
    RangeCalendarGridHead,
    RangeCalendarGridRow,
    RangeCalendarHeadCell,
    RangeCalendarNext,
    RangeCalendarPrev,
    RangeCalendarRoot,
    useDateFormatter,
    type DateRange,
} from 'reka-ui';
import { ref, shallowRef, watch } from 'vue';

type DateRangePayload = {
    from: string;
    to: string;
};

interface SchedulerDateRangePickerProps {
    fromDate: string
    toDate: string
}

const props = defineProps<SchedulerDateRangePickerProps>();

const emit = defineEmits<{
    change: [payload: DateRangePayload]
}>();

const isDatePopoverOpen = ref(false);
const isSelectingNewRange = ref(false);

const frDateFormatter = new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'UTC',
});

const calendarFormatter = useDateFormatter('fr-BE');

const formatDisplayDate = (isoDate: string) => {
    return frDateFormatter.format(new Date(`${isoDate}T00:00:00Z`));
};

const isoToCalendarDate = (iso: string) => {
    const [y, m, d] = iso.split('-').map(Number);
    return new CalendarDate(y, m, d);
};

const calendarDateToIso = (d: DateValue) =>
    `${d.year}-${String(d.month).padStart(2, '0')}-${String(d.day).padStart(2, '0')}`;

const isOutsideViewDate = (date: DateValue, monthValue: DateValue) => {
    return date.year !== monthValue.year || date.month !== monthValue.month;
};

const isMonthBoundaryCell = (
    weekDates: DateValue[],
    dayIndex: number,
    monthValue: DateValue,
    side: 'left' | 'right',
) => {
    const current = weekDates[dayIndex];

    if (!current || isOutsideViewDate(current, monthValue)) {
        return false;
    }

    if (side === 'left') {
        if (dayIndex === 0) {
            return false;
        }

        const previous = weekDates[dayIndex - 1];
        return !!previous && isOutsideViewDate(previous, monthValue);
    }

    if (dayIndex === weekDates.length - 1) {
        return false;
    }

    const next = weekDates[dayIndex + 1];
    return !!next && isOutsideViewDate(next, monthValue);
};

const buildCalendarRange = (from: string, to: string): DateRange => ({
    start: isoToCalendarDate(from),
    end: isoToCalendarDate(to),
});

const draftCalendarRange = shallowRef<DateRange>(buildCalendarRange(props.fromDate, props.toDate));

const resetDraftCalendarRange = () => {
    draftCalendarRange.value = buildCalendarRange(props.fromDate, props.toDate);
    isSelectingNewRange.value = false;
};

const onCalendarRangeChange = (range: DateRange) => {
    if (!range?.start) {
        return;
    }

    if (!isSelectingNewRange.value) {
        draftCalendarRange.value = {
            start: range.start,
            end: undefined,
        };
        isSelectingNewRange.value = true;
        return;
    }

    draftCalendarRange.value = range;

    if (!range.end) {
        return;
    }

    let normalizedFrom = calendarDateToIso(range.start);
    let normalizedTo = calendarDateToIso(range.end);

    if (normalizedFrom > normalizedTo) {
        [normalizedFrom, normalizedTo] = [normalizedTo, normalizedFrom];
    }

    isSelectingNewRange.value = false;
    isDatePopoverOpen.value = false;
    emit('change', {
        from: normalizedFrom,
        to: normalizedTo,
    });
};

watch(
    () => [props.fromDate, props.toDate],
    () => {
        resetDraftCalendarRange();
    },
);

watch(isDatePopoverOpen, (isOpen) => {
    if (isOpen) {
        resetDraftCalendarRange();
        return;
    }

    isSelectingNewRange.value = false;
});
</script>

<template>
    <PopoverRoot v-model:open="isDatePopoverOpen">
        <PopoverTrigger as-child>
            <button
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-md border border-zinc-300 bg-white px-2.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                <CalendarDays class="h-3.5 w-3.5" />
                <span>
                    {{ formatDisplayDate(props.fromDate) }} - {{ formatDisplayDate(props.toDate) }}
                </span>
            </button>
        </PopoverTrigger>

        <PopoverPortal>
            <PopoverContent
                side="bottom"
                align="start"
                :side-offset="8"
                class="z-50 rounded-xl border border-zinc-200 bg-white shadow-lg outline-none dark:border-zinc-700 dark:bg-zinc-900"
            >
                <RangeCalendarRoot
                    v-slot="{ weekDays, grid }"
                    :model-value="draftCalendarRange"
                    locale="fr-BE"
                    :week-starts-on="1"
                    :number-of-months="2"
                    fixed-weeks
                    class="flex flex-col gap-4 p-4"
                    @update:model-value="onCalendarRangeChange"
                >
                    <div class="flex gap-6">
                        <div
                            v-for="(month, index) in grid"
                            :key="month.value.toString()"
                        >
                            <div class="mb-3 flex items-center">
                                <RangeCalendarPrev
                                    v-if="index === 0"
                                    class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </RangeCalendarPrev>
                                <span v-else class="w-7" />

                                <span class="flex-1 text-center text-sm font-semibold capitalize text-zinc-800 dark:text-zinc-200">
                                    {{ calendarFormatter.custom(month.value.toDate('UTC'), { month: 'long', year: 'numeric' }) }}
                                </span>

                                <RangeCalendarNext
                                    v-if="index === grid.length - 1"
                                    class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </RangeCalendarNext>
                                <span v-else class="w-7" />
                            </div>

                            <RangeCalendarGrid class="w-full border-collapse select-none">
                                <RangeCalendarGridHead>
                                    <RangeCalendarGridRow class="mb-1 grid grid-cols-7">
                                        <RangeCalendarHeadCell
                                            v-for="day in weekDays"
                                            :key="day"
                                            class="text-center text-[11px] font-medium text-zinc-400 dark:text-zinc-500"
                                        >
                                            {{ day }}
                                        </RangeCalendarHeadCell>
                                    </RangeCalendarGridRow>
                                </RangeCalendarGridHead>
                                <RangeCalendarGridBody class="grid">
                                    <RangeCalendarGridRow
                                        v-for="(weekDates, rowIndex) in month.rows"
                                        :key="`week-${rowIndex}`"
                                        class="grid grid-cols-7"
                                    >
                                        <RangeCalendarCell
                                            v-for="(weekDate, dayIndex) in weekDates"
                                            :key="weekDate.toString()"
                                            :date="weekDate"
                                            class="my-0.5 p-0
                                                first:[&:has([data-selected])]:rounded-l-full last:[&:has([data-selected])]:rounded-r-full
                                                [&:has([data-selected][data-selection-end])]:rounded-r-full
                                                [&:not(:has([data-highlighted])):has([data-selected][data-selection-start])]:rounded-l-full
                                                first:[&:has([data-highlighted])]:rounded-l-full last:[&:has([data-highlighted])]:rounded-r-full
                                                [&:has([data-highlighted-end])]:rounded-r-full [&:has([data-highlighted-start])]:rounded-l-full
                                                [&:has([data-selected])]:bg-zinc-100 [&:has([data-highlighted])]:bg-zinc-100
                                                [&:has([data-outside-view][data-selected])]:bg-transparent! [&:has([data-outside-view][data-highlighted])]:bg-transparent!
                                                [&:has([data-outside-view])]:pointer-events-none [&:has([data-outside-view])]:bg-transparent
                                                dark:[&:has([data-selected])]:bg-zinc-800 dark:[&:has([data-highlighted])]:bg-zinc-800
                                                dark:[&:has([data-outside-view][data-selected])]:bg-transparent! dark:[&:has([data-outside-view][data-highlighted])]:bg-transparent!"
                                            :class="[
                                                isMonthBoundaryCell(weekDates, dayIndex, month.value, 'left') ? 'rounded-l-full' : '',
                                                isMonthBoundaryCell(weekDates, dayIndex, month.value, 'right') ? 'rounded-r-full' : '',
                                            ]"
                                        >
                                            <RangeCalendarCellTrigger
                                                :day="weekDate"
                                                :month="month.value"
                                                class="relative flex h-8 w-8 items-center justify-center rounded-full text-xs font-normal text-zinc-700 outline-none transition-colors
                                                    hover:bg-zinc-800 hover:text-white
                                                    focus-visible:ring-2 focus-visible:ring-zinc-400
                                                    data-selection-start:bg-zinc-800 data-selection-start:text-white
                                                    data-selection-end:bg-zinc-800 data-selection-end:text-white
                                                    data-highlighted-start:bg-zinc-800 data-highlighted-start:text-white
                                                    data-highlighted-end:bg-zinc-800 data-highlighted-end:text-white
                                                    data-unavailable:pointer-events-none data-unavailable:text-zinc-300
                                                    data-outside-view:pointer-events-none data-outside-view:bg-transparent! data-outside-view:text-transparent data-outside-view:hover:bg-transparent! dark:data-outside-view:text-transparent dark:data-outside-view:hover:text-transparent
                                                    before:absolute before:bottom-0.75 before:hidden before:h-1 before:w-1 before:rounded-full
                                                    data-today:before:block data-today:before:bg-zinc-400
                                                    dark:text-zinc-200 dark:hover:bg-zinc-200 dark:hover:text-zinc-900
                                                    dark:data-selection-start:bg-zinc-200 dark:data-selection-start:text-zinc-900
                                                    dark:data-selection-end:bg-zinc-200 dark:data-selection-end:text-zinc-900
                                                    dark:data-today:before:bg-zinc-500"
                                            />
                                        </RangeCalendarCell>
                                    </RangeCalendarGridRow>
                                </RangeCalendarGridBody>
                            </RangeCalendarGrid>
                        </div>
                    </div>
                </RangeCalendarRoot>

                <DateRangeFieldRoot
                    v-slot="{ segments }"
                    :model-value="draftCalendarRange"
                    locale="fr-BE"
                    class="flex items-center gap-2 border-t border-zinc-100 px-4 py-3 dark:border-zinc-800"
                    @update:model-value="onCalendarRangeChange"
                >
                    <div class="flex items-center rounded-md border border-zinc-200 bg-white px-2 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <template v-for="item in segments.start" :key="item.part">
                            <DateRangeFieldInput
                                v-if="item.part === 'literal'"
                                :part="item.part"
                                type="start"
                                class="text-xs text-zinc-400 dark:text-zinc-500"
                            >
                                {{ item.value }}
                            </DateRangeFieldInput>
                            <DateRangeFieldInput
                                v-else
                                :part="item.part"
                                type="start"
                                class="rounded px-0.5 text-center text-xs text-zinc-700 outline-none focus:bg-zinc-100 dark:text-zinc-200 dark:focus:bg-zinc-700"
                                :class="item.part === 'year' ? 'w-9' : 'w-6'"
                            >
                                {{ item.value }}
                            </DateRangeFieldInput>
                        </template>
                    </div>

                    <span class="text-xs text-zinc-400">–</span>

                    <div class="flex items-center rounded-md border border-zinc-200 bg-white px-2 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <template v-for="item in segments.end" :key="item.part">
                            <DateRangeFieldInput
                                v-if="item.part === 'literal'"
                                :part="item.part"
                                type="end"
                                class="text-xs text-zinc-400 dark:text-zinc-500"
                            >
                                {{ item.value }}
                            </DateRangeFieldInput>
                            <DateRangeFieldInput
                                v-else
                                :part="item.part"
                                type="end"
                                class="rounded px-0.5 text-center text-xs text-zinc-700 outline-none focus:bg-zinc-100 dark:text-zinc-200 dark:focus:bg-zinc-700"
                                :class="item.part === 'year' ? 'w-9' : 'w-6'"
                            >
                                {{ item.value }}
                            </DateRangeFieldInput>
                        </template>
                    </div>
                </DateRangeFieldRoot>
            </PopoverContent>
        </PopoverPortal>
    </PopoverRoot>
</template>
