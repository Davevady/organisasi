<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

const props = defineProps<{
    summary: {
        monthly_income: number;
        monthly_expense: number;
        net_balance: number;
    };
}>();

const formatCurrency = (value: number) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Financial Overview</CardTitle>
        </CardHeader>

        <CardContent class="space-y-5">

            <div class="flex justify-between">
                <span class="text-muted-foreground">
                    Income This Month
                </span>

                <span class="font-semibold text-green-600">
                    {{ formatCurrency(summary.monthly_income) }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-muted-foreground">
                    Expense This Month
                </span>

                <span class="font-semibold text-red-600">
                    {{ formatCurrency(summary.monthly_expense) }}
                </span>
            </div>

            <div class="border-t pt-4 flex justify-between">

                <span class="font-medium">
                    Net Balance
                </span>

                <span
                    class="font-bold text-lg"
                    :class="summary.net_balance >= 0
                        ? 'text-green-600'
                        : 'text-red-600'"
                >
                    {{ formatCurrency(summary.net_balance) }}
                </span>

            </div>

        </CardContent>
    </Card>
</template>