<script setup lang="ts">
import { computed } from "vue";
import { Input } from "@/components/ui/input";

interface Props {
    modelValue: number | null;
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: 0,
    placeholder: "",
    disabled: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: number): void;
}>();

const displayValue = computed({
    get() {
        if (!props.modelValue) return "";

        return Number(props.modelValue).toLocaleString("id-ID");
    },

    set(value: string) {
        const numeric = value.replace(/[^\d]/g, "");

        emit("update:modelValue", numeric ? Number(numeric) : 0);
    },
});
</script>

<template>
    <Input
        v-model="displayValue"
        type="text"
        inputmode="numeric"
        :placeholder="placeholder"
        :disabled="disabled"
    />
</template>