<script setup lang="ts">
import { reactive, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from "@/components/ui/table";
import { type BreadcrumbItem } from "@/types";
import ConfirmDialog from "@/components/ConfirmDialog.vue";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const props = defineProps<{
    transactions: any;
    filters: {
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Cash Transactions", href: "/cash-transaction" },
    { title: "Trash", href: "/cash-transaction/trashed" }
];

const filter = reactive({
    search: props.filters?.search ?? "",
});

const apply = () => router.get("/cash-transaction/trashed", filter, { preserveState: true, replace: true });

const openRestoreDialog = ref(false);

const selectedRestoreUuid = ref("");

const restoreForm = useForm({});

const confirmRestore = (uuid: string) => {
    selectedRestoreUuid.value = uuid;
    openRestoreDialog.value = true;
};

const restore = () => {
    if (!selectedRestoreUuid.value) return;

    restoreForm.post(
        `/cash-transaction/${selectedRestoreUuid.value}/restore`,
        {
            preserveScroll: true,
            onSuccess: () => {
                openRestoreDialog.value = false;
                selectedRestoreUuid.value = "";
            },
        },
    );
};

const openDeleteDialog = ref(false);
const selectedTransactionUuid = ref("");

const deleteForm = useForm({});
const confirmForceDelete = (uuid: string) => {
    selectedTransactionUuid.value = uuid;
    openDeleteDialog.value = true;
};

const destroy = () => {
    if (!selectedTransactionUuid.value) return;
    deleteForm.delete(`/cash-transaction/${selectedTransactionUuid.value}/force-delete`, {
        preserveScroll: true,
        onSuccess: () => {
            openDeleteDialog.value = false;
            selectedTransactionUuid.value = "";
        }
    });
};

const money = (v: number) => new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(v);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Trashed Cash Transactions" />
        <div class="p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Trashed Cash Transactions</CardTitle>
                        <p class="text-sm text-muted-foreground mt-1">List of deleted cash transactions that can be
                            restored or permanently purged.</p>
                    </div>
                    <Link href="/cash-transaction">
                        <Button variant="outline">Back to Transactions</Button>
                    </Link>
                </CardHeader>

                <CardContent>

                    <div class="flex gap-4 mb-6 max-w-sm">
                        <Input v-model="filter.search" placeholder="Search trash..." @keyup.enter="apply" />
                        <Button @click="apply">Search</Button>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Account</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead class="text-right">Amount</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-if="transactions.data.length === 0">
                                <TableCell colspan="7" class="text-center py-10">No trashed transactions found.
                                </TableCell>
                            </TableRow>

                            <TableRow v-for="t in transactions.data" :key="t.uuid">
                                <TableCell class="font-medium">{{ t.transaction_code }}</TableCell>
                                <TableCell>{{ t.transaction_date }}</TableCell>
                                <TableCell>{{ t.cash_account?.name }}</TableCell>
                                <TableCell>{{ t.category?.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="t.type === 'in' ? 'default' : 'destructive'">
                                        {{ t.type === 'in' ? 'Cash In' : 'Cash Out' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">{{ money(Number(t.amount)) }}</TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon">
                                                ⋯
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="confirmRestore(t.uuid)">
                                                Restore
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="text-red-600 font-semibold"
                                                @click="confirmForceDelete(t.uuid)">
                                                Delete Permanently
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="flex justify-between mt-6 text-sm">
                        <div>Showing {{ transactions.from }} - {{ transactions.to }} of {{ transactions.total }}</div>
                        <div class="flex gap-2">
                            <Link v-for="link in transactions.links" :key="link.label" :href="link.url || '#'"
                                v-html="link.label" class="px-3 py-1 border rounded"
                                :class="{ 'bg-primary text-primary-foreground': link.active, 'pointer-events-none opacity-50': !link.url }" />
                        </div>
                    </div>

                </CardContent>
            </Card>
        </div>
        <!-- Restore Dialog -->
        <ConfirmDialog :open="openRestoreDialog" title="Restore Transaction"
            description="Are you sure you want to restore this transaction? This transaction will become active again."
            confirm-text="Restore" :loading="restoreForm.processing" @update:open="openRestoreDialog = $event"
            @confirm="restore" />

        <!-- Force Delete Dialog -->
        <ConfirmDialog :open="openDeleteDialog" title="Delete Permanently"
            description="This action cannot be undone. The transaction will be permanently removed from the database."
            confirm-text="Delete Permanently" confirm-variant="destructive" :loading="deleteForm.processing"
            @update:open="openDeleteDialog = $event" @confirm="destroy" />
    </AppLayout>
</template>
