<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import AppLayout from "@/layouts/AppLayout.vue";

import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/InputError.vue";

import CurrencyInput from "@/components/CurrencyInput.vue";
import { type BreadcrumbItem } from "@/types";

interface CashAccount {
    id: number;
    name: string;
    code: string;
}

interface Category {
    id: number;
    name: string;
    type: string;
}

interface Props {
    accounts: CashAccount[];
    categories: Category[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Cash Transactions",
        href: "/cash-transaction",
    },
    {
        title: "Add Transaction",
        href: "/cash-transaction/create",
    },
];

const form = useForm({
    cash_account_id: "",
    category_id: "",
    type: "in",
    amount: 0,
    transaction_date: new Date().toISOString().split("T")[0],
    description: "",
    notes: "",
    reference_number: "",
});

const submit = () => {
    form.post("/cash-transaction");
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Add Cash Transaction" />
        
        <div class="p-6">
            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle>Add Cash Transaction</CardTitle>
                    <CardDescription>
                        Record a new incoming or outgoing cash transaction.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label>Transaction Type</Label>
                                <select v-model="form.type" class="h-10 px-3 border rounded-md">
                                    <option value="in">Cash In (+)</option>
                                    <option value="out">Cash Out (-)</option>
                                </select>
                                <InputError :message="form.errors.type" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Date</Label>
                                <Input type="date" v-model="form.transaction_date" />
                                <InputError :message="form.errors.transaction_date" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label>Account</Label>
                                <select v-model="form.cash_account_id" class="h-10 px-3 border rounded-md">
                                    <option value="" disabled>Select Account</option>
                                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                                        {{ account.code }} - {{ account.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.cash_account_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Category</Label>
                                <select v-model="form.category_id" class="h-10 px-3 border rounded-md">
                                    <option value="" disabled>Select Category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category_id" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Description</Label>
                            <Input v-model="form.description" placeholder="Short description of the transaction" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Amount</Label>
                            <CurrencyInput v-model="form.amount" placeholder="Transaction Amount" />
                            <InputError :message="form.errors.amount" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label>Reference Number</Label>
                                <Input v-model="form.reference_number" placeholder="Receipt or invoice number (optional)" />
                                <InputError :message="form.errors.reference_number" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Additional Notes</Label>
                            <textarea v-model="form.notes" rows="3" class="px-3 py-2 border rounded-md" placeholder="Optional notes..."></textarea>
                            <InputError :message="form.errors.notes" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" onclick="history.back()">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Save Transaction
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>