<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

import { Badge } from "@/components/ui/badge";

interface Item {
    id: number;
    code: string;
    name: string;
    current_stock: number;
    minimum_stock: number;
    unit?: {
        name: string;
        symbol: string;
    };
}

defineProps<{
    items: Item[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Low Stock Items</CardTitle>
        </CardHeader>

        <CardContent>

            <div
                v-if="items.length"
                class="space-y-3"
            >
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-center justify-between rounded-lg border p-3"
                >
                    <div>
                        <p class="font-medium">
                            {{ item.name }}
                        </p>

                        <p class="text-sm text-muted-foreground">
                            {{ item.current_stock }}
                            {{ item.unit?.symbol }}
                        </p>
                    </div>

                    <Badge variant="destructive">
                        Low
                    </Badge>

                </div>
            </div>

            <div
                v-else
                class="py-8 text-center text-muted-foreground"
            >
                🎉 No low stock items.
            </div>

        </CardContent>
    </Card>
</template>