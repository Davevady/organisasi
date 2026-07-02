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
    uuid: string;
    name: string;
    code: string;
    type: "bank" | "cash";
    account_number: string | null;
    balance: number;
    description: string | null;
    is_active: boolean;
}

const props = defineProps<{
    cashAccount: CashAccount;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Cash Accounts",
        href: "/cash-account",
    },
    {
        title: "Edit Account",
        href: `/cash-account/${props.cashAccount.uuid}/edit`,
    },
];

const form = useForm({
    name: props.cashAccount.name,
    code: props.cashAccount.code,
    type: props.cashAccount.type,
    account_number: props.cashAccount.account_number ?? "",
    balance: props.cashAccount.balance,
    description: props.cashAccount.description ?? "",
    is_active: props.cashAccount.is_active,
});

const submit = () => {
    form.put(`/cash-account/${props.cashAccount.uuid}`);
};
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Cash Account" />
        <div class="p-6">
            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle>Edit Cash Account</CardTitle>
                    <CardDescription>
                        Update cash or bank account information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label>Name</Label>
                            <Input v-model="form.name" placeholder="Cash Account Name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label>Code</Label>
                                <Input v-model="form.code" />
                                <InputError :message="form.errors.code" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Type</Label>
                                <select v-model="form.type" class="h-10 rounded-md border px-3">
                                    <option value="cash">
                                        Cash
                                    </option>
                                    <option value="bank">
                                        Bank
                                    </option>
                                </select>
                                <InputError :message="form.errors.type" />
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label>Account Number</Label>
                            <Input v-model="form.account_number" />
                            <InputError :message="form.errors.account_number" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Balance</Label>
                            <CurrencyInput v-model="form.balance" placeholder="Balance" />
                            <InputError :message="form.errors.balance" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Description</Label>
                            <textarea v-model="form.description" rows="4" class="rounded-md border px-3 py-2" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input id="active" type="checkbox" v-model="form.is_active" />
                            <Label for="active">
                                Active
                            </Label>
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" onclick="history.back()">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update Account
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>