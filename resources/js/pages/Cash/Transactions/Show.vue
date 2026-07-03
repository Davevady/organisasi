<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { type BreadcrumbItem } from "@/types";

const props = defineProps<{
    transaction: {
        uuid: string;
        transaction_code: string;
        type: "in" | "out";
        amount: string;
        transaction_date: string;
        description: string;
        notes: string | null;
        reference_number: string | null;
        created_at: string;
        cash_account: {
            name: string;
            code: string;
        };
        category: {
            name: string;
        };
        recorder: {
            name: string;
        };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Cash Transactions", href: "/cash-transaction" },
    { title: "Transaction Details", href: `/cash-transaction/${props.transaction.uuid}` },
];

const money = (v: number) => new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(v);
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Transaction Details" />
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Transaction Details
                    </h1>
                </div>
                <Link href="/cash-transaction">
                    <Button variant="outline">Back to List</Button>
                </Link>
            </div>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <CardTitle class="text-base font-semibold">
                        {{ transaction.transaction_code }}
                    </CardTitle>
                    <Badge :variant="transaction.type === 'in' ? 'default' : 'destructive'">
                        {{ transaction.type === 'in' ? 'Cash In' : 'Cash Out' }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="flex flex-col items-center justify-center py-6 bg-muted/30 rounded-lg">
                        <span class="text-sm text-muted-foreground">Amount</span>
                        <span :class="[
                            'text-4xl font-extrabold tracking-tight mt-1',
                            transaction.type === 'in' ? 'text-green-600' : 'text-red-600'
                        ]">
                            {{ transaction.type === 'in' ? '+' : '-' }}
                            {{ money(Number(transaction.amount)) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground block">Transaction Date</span>
                            <span class="font-medium mt-1 block">{{ formatDate(transaction.transaction_date) }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block">Cash Account</span>
                            <span class="font-medium mt-1 block">
                                {{ transaction.cash_account.name }} ({{ transaction.cash_account.code }})
                            </span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block">Category</span>
                            <span class="font-medium mt-1 block">{{ transaction.category.name }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block">Recorded By</span>
                            <span class="font-medium mt-1 block">{{ transaction.recorder.name }}</span>
                        </div>
                    </div>

                    <Separator />

                    <div class="space-y-4">
                        <div>
                            <span class="text-muted-foreground text-sm block">Description</span>
                            <p class="text-sm mt-1 text-foreground font-medium bg-muted/10 p-3 rounded border">
                                {{ transaction.description }}
                            </p>
                        </div>

                        <div v-if="transaction.reference_number">
                            <span class="text-muted-foreground text-sm block">Reference Number</span>
                            <span class="text-sm font-medium mt-1 block">{{ transaction.reference_number }}</span>
                        </div>

                        <div v-if="transaction.notes">
                            <span class="text-muted-foreground text-sm block">Notes</span>
                            <p
                                class="text-sm mt-1 text-muted-foreground whitespace-pre-line bg-muted/10 p-3 rounded border">
                                {{ transaction.notes }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
