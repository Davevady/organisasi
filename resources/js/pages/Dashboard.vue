<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { dashboard } from "@/routes";
import { Head, router } from "@inertiajs/vue3";
import { type BreadcrumbItem } from "@/types";

import DateRangeFilter from "@/components/filter/DateRangeFilter.vue";
import SummaryCards from "@/components/dashboard/SummaryCards.vue";
import CashFlowChart from "@/components/dashboard/CashFlowChart.vue";
import FinanceSummary from "@/components/dashboard/FinanceSummary.vue";
import PaymentSummary from "@/components/dashboard/PaymentSummary.vue";
import RecentTransactions from "@/components/dashboard/RecentTransactions.vue";
import RecentMembers from "@/components/dashboard/RecentMembers.vue";
import RecentPayments from "@/components/dashboard/RecentPayments.vue";
import InventoryAlert from "@/components/dashboard/InventoryAlert.vue";
import { ref, watch } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Dashboard",
        href: dashboard().url,
    },
];

defineProps<{
    summary: any;
    paymentSummary: any;
    recentTransactions: any[];
    recentPayments: any[];
    recentMembers: any[];
    lowStockItems: any[];
    cashFlow: {
        date: string;
        income: number;
        expense: number;
    }[];
}>();

const today = new Date();

const dateRange = ref({
    start: new Date(today.getFullYear(), today.getMonth(), 1)
        .toISOString()
        .split("T")[0],

    end: today.toISOString().split("T")[0],
});

const loadDashboard = (range: {
    start: string;
    end: string;
}) => {

    router.get(
        dashboard().url,
        {
            start_date: range.start,
            end_date: range.end,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );

};

watch(
    dateRange,
    () => {
        loadDashboard(dateRange.value);
    },
    {
        deep: true,
        immediate: true, // <-- atau default watchEffect
    }
);
</script>

<template>

    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <SummaryCards :summary="summary" />

            <DateRangeFilter v-model="dateRange" @apply="loadDashboard" />

            <CashFlowChart :cash-flow="cashFlow" />

            <div class="grid gap-6 lg:grid-cols-2">
                <FinanceSummary :summary="summary" />
                <PaymentSummary :payment-summary="paymentSummary" />
            </div>

            <RecentTransactions :transactions="recentTransactions" />

            <div class="grid gap-6 lg:grid-cols-2">
                <InventoryAlert :items="lowStockItems" />
                <RecentMembers :members="recentMembers" />
            </div>

            <RecentPayments :payments="recentPayments" />

        </div>
    </AppLayout>
</template>