<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

import { Badge } from "@/components/ui/badge";

defineProps<{
    paymentSummary: {
        paid: number;
        unpaid: number;
        partial: number;
        late: number;
    };
}>();
</script>

<template>
    <Card>

        <CardHeader>
            <CardTitle>
                Member Payment Status
            </CardTitle>
        </CardHeader>

        <CardContent class="space-y-4">

            <div class="flex justify-between items-center">
                <span>Paid</span>

                <Badge variant="default">
                    {{ paymentSummary.paid }}
                </Badge>
            </div>

            <div class="flex justify-between items-center">
                <span>Unpaid</span>

                <Badge variant="secondary">
                    {{ paymentSummary.unpaid }}
                </Badge>
            </div>

            <div class="flex justify-between items-center">
                <span>Partial</span>

                <Badge variant="outline">
                    {{ paymentSummary.partial }}
                </Badge>
            </div>

            <div class="flex justify-between items-center">
                <span>Late</span>

                <Badge variant="destructive">
                    {{ paymentSummary.late }}
                </Badge>
            </div>

        </CardContent>

    </Card>
</template>