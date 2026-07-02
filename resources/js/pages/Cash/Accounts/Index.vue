<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { type BreadcrumbItem } from "@/types";

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

import ConfirmDeleteDialog from "@/components/ConfirmDeleteDialog.vue";

import { formatCurrency } from "@/utils/formatter";

interface CashAccount {
    uuid: string;
    name: string;
    code: string;
    type: "bank" | "cash";
    account_number: string | null;
    balance: number;
    is_active: boolean;
}

interface Props {
    cashAccounts?: {
        data: CashAccount[];
    };
}

defineProps<Props>();

const openDeleteDialog = ref(false);

const selectedAccount = ref<CashAccount | null>(null);

const confirmDelete = (account: CashAccount) => {
    selectedAccount.value = account;
    openDeleteDialog.value = true;
};

const deleteAccount = () => {
    if (!selectedAccount.value) return;

    router.delete(`/cash-account/${selectedAccount.value.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            openDeleteDialog.value = false;
            selectedAccount.value = null;
        },
    });
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Cash Accounts",
        href: "/cash/accounts",
    },
];
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Cash Accounts" />
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Cash Accounts
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Manage bank and cash accounts.
                    </p>
                </div>
                <Button as-child>
                    <Link href="/cash-account/create">
                        Add Account
                    </Link>
                </Button>
            </div>
            <!-- Table Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Account List</CardTitle>
                    <Input placeholder="Search..." class="w-72" />
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/40">
                                <tr>
                                    <th class="px-4 py-3 text-left">Code</th>
                                    <th class="px-4 py-3 text-left">Name</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Account Number</th>
                                    <th class="px-4 py-3 text-right">Balance</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!cashAccounts || cashAccounts.data.length === 0">
                                    <td colspan="7" class="py-10 text-center text-muted-foreground">
                                        No cash accounts found.
                                    </td>
                                </tr>
                                <tr v-for="account in cashAccounts?.data" :key="account.uuid"
                                    class="border-b hover:bg-muted/30">
                                    <td class="px-4 py-3">
                                        {{ account.code }}
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        {{ account.name }}
                                    </td>
                                    <td class="px-4 py-3 uppercase">
                                        {{ account.type }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ account.account_number ?? "-" }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(account.balance) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            account.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700'
                                        ]">
                                            {{ account.is_active ? "Active" : "Inactive" }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    ⋯
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/cash-account/${account.uuid}/edit`">
                                                        Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="text-red-600" @click="confirmDelete(account)">
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
            <ConfirmDeleteDialog v-model:open="openDeleteDialog" title="Delete Cash Account"
                :description="`Are you sure you want to delete '${selectedAccount?.name}'? This action cannot be undone.`"
                @confirm="deleteAccount" />
        </div>
    </AppLayout>
</template>