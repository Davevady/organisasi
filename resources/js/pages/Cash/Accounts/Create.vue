<script setup lang="ts">
import { ref, watch } from "vue";
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

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Cash Accounts",
        href: "/cash-account",
    },
    {
        title: "Add Account",
        href: "/cash-account/create",
    },
];

const form = useForm({
    name: "",
    code: "",
    type: "cash",
    account_number: "",
    balance: 0,
    description: "",
    is_active: true,
});

const submit = () => {
    form.post("/cash-account");
};
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Add Cash Account" />
        <div class="p-6">
            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle>Add Cash Account</CardTitle>
                    <CardDescription>
                        Create a new cash or bank account.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label>Name</Label>
                            <Input v-model="form.name" placeholder="Cash Account Name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label>Code</Label>
                                <Input v-model="form.code" placeholder="ACC001" />
                                <InputError :message="form.errors.code" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Type</Label>
                                <select v-model="form.type" class="border rounded-md h-10 px-3">
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
                            <Input v-model="form.account_number" placeholder="Optional" />
                            <InputError :message="form.errors.account_number" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Opening Balance</Label>
                            <CurrencyInput v-model="form.balance" placeholder="Opening Balance" />
                            <InputError :message="form.errors.balance" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Description</Label>
                            <textarea v-model="form.description" rows="4" class="border rounded-md px-3 py-2" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" onclick="history.back()">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Save Account
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>