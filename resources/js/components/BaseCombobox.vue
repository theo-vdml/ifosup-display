<script setup lang="ts" generic="T extends string | number | object">
    import { ref, computed } from 'vue'
    import {
        Combobox,
        ComboboxInput,
        ComboboxButton,
        ComboboxOptions,
        ComboboxOption,
        TransitionRoot
    } from '@headlessui/vue'
    import { Check, ChevronsUpDown } from 'lucide-vue-next'
    import { cn } from "@/lib/utils" // Reusing your utility

    interface BaseComboboxProps {
        modelValue: T | null
        options: T[]
        displayValue?: (option: T) => string
        filterFunction?: (option: T, query: string) => boolean
        placeholder?: string
    }

    const props = withDefaults(defineProps<BaseComboboxProps>(), {
        placeholder: 'Select option...'
    });

    const emit = defineEmits<{
        'update:modelValue': [value: T | null]
    }>();

    const query = ref('');

    const getDisplayString = (option: T | null): string => {
        if (!option) return '';
        return props.displayValue ? props.displayValue(option) : String(option);
    }

    const filteredOptions = computed(() => {
        const q = query.value.toLowerCase();
        if (q === '') return props.options;
        if (props.filterFunction) return props.options.filter(opt => props.filterFunction!(opt, query.value));
        return props.options.filter((option) => getDisplayString(option).toLowerCase().includes(q));
    })

    const getOptionKey = (option: T, index: number) => {
        if (typeof option === 'object' && option !== null && 'id' in option) return (option as any).id;
        return index;
    }
</script>

<template>
    <Combobox :model-value="modelValue" @update:model-value="val => emit('update:modelValue', val)" as="div"
        class="w-full" nullable>
        <div class="relative">
            <ComboboxInput :class="cn(
                'placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                'pr-10'
            )" @change="query = $event.target.value" :display-value="(val) => getDisplayString(val as T)"
                :placeholder="placeholder" />

            <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2 cursor-pointer">
                <ChevronsUpDown class="h-4 w-4 text-muted-foreground/50" aria-hidden="true" />
            </ComboboxButton>

            <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0"
                @after-leave="query = ''">

                <ComboboxOptions
                    class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-input bg-neutral-900 p-1 text-sm shadow-md outline-none">

                    <div v-if="filteredOptions.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                        No results found.
                    </div>

                    <ComboboxOption v-for="(option, index) in filteredOptions" :key="getOptionKey(option, index)"
                        :value="option" v-slot="{ selected, active }">
                        <li :class="cn(
                            'relative flex w-full select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none transition-colors cursor-pointer',
                            active ? 'bg-accent text-accent-foreground' : 'text-foreground'
                        )">
                            <span v-if="selected" class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                                <Check class="h-4 w-4" />
                            </span>

                            <span :class="cn('block truncate', selected ? 'font-medium' : 'font-normal')">
                                {{ getDisplayString(option) }}
                            </span>
                        </li>
                    </ComboboxOption>
                </ComboboxOptions>
            </TransitionRoot>
        </div>
    </Combobox>
</template>