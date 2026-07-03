<script setup lang="ts">
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps<{
    status: number;
}>();

type ErrorMessage = {
    title: string;
    description: string;
};

const messages: Record<number, ErrorMessage> = {
    403: {
        title: "403",
        description: "You are not authorized to access this page.",
    },
    404: {
        title: "404",
        description: "The page you are looking for could not be found.",
    },
    500: {
        title: "500",
        description: "Something went wrong on our server.",
    },
    503: {
        title: "503",
        description: "Service is temporarily unavailable.",
    },
};

const error = computed(() => {
    return (
        messages[props.status] ?? {
            title: String(props.status),
            description: "An unexpected error occurred.",
        }
    );
});
</script>

<template>
    <Head :title="error.title" />

    <div class="flex min-h-screen items-center justify-center">
        <div class="text-center">
            <h1 class="text-8xl font-bold">{{ error.title }}</h1>

            <p class="mt-4 text-muted-foreground">
                {{ error.description }}
            </p>

            <Link
                href="/dashboard"
                class="mt-8 inline-flex rounded-md px-4 py-2 text-white"
            >
                Back to Dashboard
            </Link>
        </div>
    </div>
</template>