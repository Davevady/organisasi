<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { formatCurrency } from "@/utils/formatter";

defineProps<{
    summary: any;
}>();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        <Card>
            <CardHeader>
                <CardTitle>Total Members</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-3xl font-bold whitespace-nowrap">
                    {{ summary.total_members }}
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Active Members</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-3xl font-bold whitespace-nowrap">
                    {{ summary.active_members }}
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Cash Balance</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-3xl font-bold whitespace-nowrap">
                    {{ formatCurrency(summary.cash_balance) }}
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Inventory</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="text-3xl font-bold whitespace-nowrap">
                    {{ summary.inventory_items }}
                </div>
            </CardContent>
        </Card>

    </div>
</template>