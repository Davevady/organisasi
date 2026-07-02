<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/components/ui/alert-dialog";

interface Props {
    open: boolean;
    title?: string;
    description?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: "Delete Data",
    description: "Are you sure you want to delete this data?",
    loading: false,
});

const emit = defineEmits<{
    (e: "update:open", value: boolean): void;
    (e: "confirm"): void;
}>();
</script>

<template>
    <AlertDialog :open="props.open" @update:open="emit('update:open', $event)">
        <AlertDialogContent>

            <AlertDialogHeader>

                <AlertDialogTitle>
                    {{ title }}
                </AlertDialogTitle>

                <AlertDialogDescription>
                    {{ description }}
                </AlertDialogDescription>

            </AlertDialogHeader>

            <AlertDialogFooter>

                <AlertDialogCancel>
                    Cancel
                </AlertDialogCancel>

                <AlertDialogAction class="bg-red-600 hover:bg-red-700" :disabled="loading" @click="emit('confirm')">
                    Delete
                </AlertDialogAction>

            </AlertDialogFooter>

        </AlertDialogContent>
    </AlertDialog>
</template>