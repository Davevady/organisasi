<script setup lang="ts">
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";
import type { ApexOptions } from "apexcharts";

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/components/ui/card";

interface CashFlowItem {
    date: string;
    income: number;
    expense: number;
}

const props = defineProps<{
    cashFlow: CashFlowItem[];
}>();

const series = computed(() => [
    {
        name: "Income",
        data: props.cashFlow.map(item => item.income),
    },
    {
        name: "Expense",
        data: props.cashFlow.map(item => item.expense),
    },
]);

const categories = computed(() =>
    props.cashFlow.map(item =>
        new Date(item.date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
        })
    )
);

const total = computed(() => categories.value.length);

const maxLabels = computed(() => {
    if (total.value <= 10) return total.value;
    if (total.value <= 30) return 5;
    return 10;
});

const step = computed(() =>
    Math.ceil(total.value / maxLabels.value)
);

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        toolbar: {
            show: false,
        },
        zoom: {
            enabled: false,
        },
        foreColor: "#9ca3af",
    },

    stroke: {
        curve: "smooth",
        width: 3,
    },

    xaxis: {
        categories: categories.value,

        labels: {
            formatter(value, timestamp, opts) {

                if (!opts) return String(value);

                const index = opts.dataPointIndex;

                if (
                    index !== total.value - 1 &&
                    index % step.value !== 0
                ) {
                    return "";
                }

                return String(value);
            }
        },
    },

    yaxis: {
        labels: {
            formatter(value: number) {
                return "Rp " + value.toLocaleString("id-ID");
            },
        },
    },

    tooltip: {
        y: {
            formatter(value: number) {
                return "Rp " + value.toLocaleString("id-ID");
            },
        },
    },

    colors: [
        "#22c55e",
        "#ef4444",
    ],

    legend: {
        position: "top",
        horizontalAlign: "right",
    },

    grid: {
        borderColor: "#27272a",
    },

    dataLabels: {
        enabled: false,
    },

    fill: {
        opacity: 1,
    },
}));
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Cash Flow</CardTitle>
            <CardDescription>
                Daily cash flow based on selected date range
            </CardDescription>
        </CardHeader>

        <CardContent>
            <VueApexCharts type="line" height="350" :options="chartOptions" :series="series" />
        </CardContent>
    </Card>
</template>