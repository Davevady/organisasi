<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { CalendarIcon } from "lucide-vue-next";

import { Button } from "@/components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Input } from "@/components/ui/input";

interface DateRange {
    start: string;
    end: string;
}

type PresetValue = (typeof presets)[number]["value"];

const props = defineProps<{
    modelValue: DateRange;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: DateRange): void;
    (e: "apply", value: DateRange): void;
}>();

const start = ref(props.modelValue.start);
const end = ref(props.modelValue.end);

const activePreset = ref<PresetValue | null>(null);

watch(
    () => props.modelValue,
    (value) => {
        start.value = value.start;
        end.value = value.end;
    }
);

const formatDate = (date: string) => {
    if (!date) return "";

    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

const label = computed(() => {
    return `${formatDate(start.value)} - ${formatDate(end.value)}`;
});

const apply = () => {
    const value = {
        start: start.value,
        end: end.value,
    };

    emit("update:modelValue", value);
    emit("apply", value);
};

const presets = [
    { label: "1D", value: 1 },
    { label: "7D", value: 7 },
    { label: "30D", value: 30 },
    { label: "90D", value: 90 },
    { label: "This Week", value: "week" },
    { label: "This Month", value: "month" },
    { label: "This Year", value: "year" },
] as const;

const preset = (value: PresetValue) => {

    activePreset.value = value;

    const today = new Date();

    let startDate = new Date(today);

    if (typeof value === "number") {

        if (value > 1) {
            startDate.setDate(today.getDate() - (value - 1));
        }

    } else if (value === "week") {

        const day = today.getDay();
        const diff = day === 0 ? 6 : day - 1;

        startDate.setDate(today.getDate() - diff);

    } else if (value === "month") {

        startDate = new Date(
            today.getFullYear(),
            today.getMonth(),
            1
        );

    } else if (value === "year") {

        startDate = new Date(
            today.getFullYear(),
            0,
            1
        );

    }

    start.value = startDate.toISOString().split("T")[0];
    end.value = today.toISOString().split("T")[0];
};

watch([start, end], () => {
    activePreset.value = null;
});
</script>

<template>
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <Popover>
            <PopoverTrigger as-child>
                <Button variant="outline" class="justify-start gap-2">
                    <CalendarIcon class="h-4 w-4" />
                    {{ label }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80 space-y-4">

                <div class="text-sm font-medium">Quick Select</div>
                <div class="flex justify-between gap-2">
                    <div class="flex flex-wrap gap-2">
                        <Button v-for="presetItem in presets" :key="presetItem.label" size="sm"
                            :variant="activePreset === presetItem.value ? 'default' : 'outline'"
                            @click="preset(presetItem.value)">
                            {{ presetItem.label }}
                        </Button>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Start Date
                    </label>
                    <Input type="date" v-model="start" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        End Date
                    </label>
                    <Input type="date" v-model="end" />
                </div>
                <Button class="w-full" @click="apply">
                    Apply
                </Button>
            </PopoverContent>
        </Popover>
    </div>
</template>