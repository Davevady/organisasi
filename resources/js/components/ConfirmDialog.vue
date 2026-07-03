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
    confirmText?: string;
    confirmVariant?: 'destructive' | 'default';
}

const props = withDefaults(defineProps<Props>(), {
    title: "Confirm Action",
    description: "Are you sure you want to proceed?",
    loading: false,
    confirmText: "Confirm",
    confirmVariant: "default",
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
                <AlertDialogAction 
                    :class="confirmVariant === 'destructive' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-primary text-primary-foreground hover:bg-primary/90'" 
                    :disabled="loading" 
                    @click="emit('confirm')"
                >
                    {{ confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
