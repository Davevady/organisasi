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

interface Payment {
    id: number;
    payment_code: string;
    amount: number;
    payment_date: string;
    status: "paid" | "unpaid" | "partial" | "late";
    member?: {
        name: string;
        member_code: string;
    };
}

defineProps<{
    payments: Payment[];
}>();

const formatCurrency = (value: number) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

const badgeVariant = (status: string) => {
    switch (status) {
        case "paid":
            return "default";

        case "partial":
            return "secondary";

        case "late":
            return "destructive";

        default:
            return "outline";
    }
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Recent Member Payments</CardTitle>
        </CardHeader>

        <CardContent>
            <Table>

                <TableHeader>

                    <TableRow>
                        <TableHead>Member</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Status</TableHead>
                    </TableRow>

                </TableHeader>

                <TableBody>

                    <TableRow
                        v-for="payment in payments"
                        :key="payment.id"
                    >

                        <TableCell>

                            <div>

                                <p class="font-medium">
                                    {{ payment.member?.name }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    {{ payment.payment_code }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    {{ payment.payment_date }}
                                </p>

                            </div>

                        </TableCell>

                        <TableCell class="font-semibold">
                            {{ formatCurrency(payment.amount) }}
                        </TableCell>

                        <TableCell>

                            <Badge
                                :variant="badgeVariant(payment.status)"
                            >
                                {{ payment.status }}
                            </Badge>

                        </TableCell>

                    </TableRow>

                    <TableRow v-if="!payments.length">

                        <TableCell
                            colspan="3"
                            class="text-center py-8 text-muted-foreground"
                        >
                            No payments found.
                        </TableCell>

                    </TableRow>

                </TableBody>

            </Table>
        </CardContent>
    </Card>
</template>