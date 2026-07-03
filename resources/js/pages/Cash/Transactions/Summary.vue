<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { type BreadcrumbItem } from "@/types";
import { formatCurrency } from "@/utils/formatter";

const props = defineProps<{
    summary: {
        total_in: number;
        total_out: number;
        balance: number;
        transaction_count: number;
        today_in?: number;
        today_out?: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Cash Transactions", href: "/cash-transaction" },
    { title: "Summary", href: "/cash-transaction/summary" },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Cash Transaction Summary" />

        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Cash Transaction Summary
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Overview of cash flows.
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" as-child>
                        <Link href="/cash-transaction">
                            View Transactions
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link href="/cash-transaction/create">
                            Add Transaction
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Total Cash In</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.total_in) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Total Cash Out</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.total_out) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Current Balance</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.balance) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Total Transactions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ summary.transaction_count }}</div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Today's Summary</CardTitle>
                </CardHeader>

                <CardContent class="flex gap-6">
                    <div>
                        <p class="text-sm text-muted-foreground mb-2">Cash In</p>
                        <Badge class="bg-green-100 text-green-800 hover:bg-green-200 border-green-200">
                            {{ formatCurrency(summary.today_in ?? 0) }}
                        </Badge>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground mb-2">Cash Out</p>
                        <Badge variant="destructive">
                            {{ formatCurrency(summary.today_out ?? 0) }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
