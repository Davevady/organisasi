<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

import { Badge } from "@/components/ui/badge";
import { formatCurrency } from "@/utils/formatter";

interface Transaction {
    id: number;
    transaction_code: string;
    transaction_date: string;
    description: string;
    amount: number;
    type: "in" | "out";
    category?: {
        name: string;
    };
    cash_account?: {
        name: string;
    };
}

defineProps<{
    transactions: Transaction[];
}>();
</script>
<template>
    <Card>
        <CardHeader>
            <CardTitle>Recent Cash Transactions</CardTitle>
        </CardHeader>

        <CardContent class="p-0">
            <div class="w-full overflow-x-auto">
                <Table class="w-full min-w-[700px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Transaction</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Type</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="transaction in transactions" :key="transaction.id">
                            <TableCell>
                                <div>
                                    <p class="font-medium">
                                        {{ transaction.description }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ transaction.transaction_code }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ transaction.transaction_date }}
                                    </p>
                                </div>
                            </TableCell>
                            <TableCell>
                                {{ transaction.category?.name ?? "-" }}
                            </TableCell>
                            <TableCell class="font-semibold whitespace-nowrap" :class="transaction.type === 'in'
                                ? 'text-green-600'
                                : 'text-red-600'">
                                {{ formatCurrency(transaction.amount) }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="transaction.type === 'in'
                                    ? 'default'
                                    : 'destructive'">
                                    {{ transaction.type === "in" ? "Income" : "Expense" }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!transactions.length">
                            <TableCell colspan="4" class="text-center text-muted-foreground py-8">
                                No transactions found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

            </div>
        </CardContent>
    </Card>
</template>