<script setup lang="ts">
import { reactive, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Table,TableBody,TableCell,TableHead,TableHeader,TableRow
} from "@/components/ui/table";
import { type BreadcrumbItem } from "@/types";
import ConfirmDeleteDialog from "@/components/ConfirmDeleteDialog.vue";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const props = defineProps<{
  transactions:any;
  cashAccounts:{id:number;name:string}[];
  categories:{id:number;name:string}[];
  filters:{
    search?:string;
    type?:string;
    cash_account?:string;
    category?:string;
  };
}>();

const breadcrumbs:BreadcrumbItem[]=[{title:"Cash Transactions",href:"/cash-transaction"}];

const filter=reactive({
 search:props.filters?.search??"",
 type:props.filters?.type??"",
 cash_account:props.filters?.cash_account??"",
 category:props.filters?.category??"",
});

const apply=()=>router.get("/cash-transaction",filter,{preserveState:true,replace:true});

const openDeleteDialog = ref(false);
const selectedTransactionUuid = ref("");

const deleteForm = useForm({});
const confirmDelete = (uuid: string) => {
  selectedTransactionUuid.value = uuid;
  openDeleteDialog.value = true;
};

const destroy = () => {
  if (!selectedTransactionUuid.value) return;
  deleteForm.delete(`/cash-transaction/${selectedTransactionUuid.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      openDeleteDialog.value = false;
      selectedTransactionUuid.value = "";
    }
  });
};

const money=(v:number)=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR"}).format(v);
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
<Head title="Cash Transactions"/>
<div class="p-6">
<Card>
<CardHeader class="flex flex-row items-center justify-between">
<CardTitle>Cash Transactions</CardTitle>
<div class="flex gap-2">
<Link href="/cash-transaction/trashed">
<Button variant="outline">View Trash</Button>
</Link>
<Link href="/cash-transaction/create">
<Button>Add Transaction</Button>
</Link>
</div>
</CardHeader>

<CardContent>

<div class="grid md:grid-cols-4 gap-4 mb-6">
<Input v-model="filter.search" placeholder="Search..." @keyup.enter="apply"/>
<select class="border rounded-md h-10 px-3" v-model="filter.type" @change="apply">
<option value="">All Type</option>
<option value="in">Cash In</option>
<option value="out">Cash Out</option>
</select>

<select class="border rounded-md h-10 px-3" v-model="filter.cash_account" @change="apply">
<option value="">All Account</option>
<option v-for="a in cashAccounts" :key="a.id" :value="a.id">{{a.name}}</option>
</select>

<select class="border rounded-md h-10 px-3" v-model="filter.category" @change="apply">
<option value="">All Category</option>
<option v-for="c in categories" :key="c.id" :value="c.id">{{c.name}}</option>
</select>
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
<TableHead>Action</TableHead>
</TableRow>
</TableHeader>

<TableBody>
<TableRow v-slot="{}" v-if="transactions.data.length===0">
<TableCell colspan="7" class="text-center py-10">No transaction found.</TableCell>
</TableRow>

<TableRow v-for="t in transactions.data" :key="t.uuid">
<TableCell>{{t.transaction_code}}</TableCell>
<TableCell>{{t.transaction_date}}</TableCell>
<TableCell>{{t.cash_account?.name}}</TableCell>
<TableCell>{{t.category?.name}}</TableCell>
<TableCell>
<Badge :variant="t.type==='in'?'default':'destructive'">
{{t.type==='in'?'Cash In':'Cash Out'}}
</Badge>
</TableCell>
<TableCell class="text-right">{{money(Number(t.amount))}}</TableCell>
<TableCell class="text-right">
<DropdownMenu>
    <DropdownMenuTrigger as-child>
        <Button variant="ghost" size="icon">
            ⋯
        </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
        <DropdownMenuItem as-child>
            <Link :href="`/cash-transaction/${t.uuid}`">
                Detail
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem class="text-red-600" @click="confirmDelete(t.uuid)">
            Delete
        </DropdownMenuItem>
    </DropdownMenuContent>
</DropdownMenu>
</TableCell>
</TableRow>
</TableBody>
</Table>

<div class="flex justify-between mt-6 text-sm">
<div>Showing {{transactions.from}} - {{transactions.to}} of {{transactions.total}}</div>
<div class="flex gap-2">
<Link v-for="link in transactions.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
class="px-3 py-1 border rounded"
:class="{'bg-primary text-primary-foreground':link.active,'pointer-events-none opacity-50':!link.url}"/>
</div>
</div>

</CardContent>
</Card>
</div>
<ConfirmDeleteDialog
  v-model:open="openDeleteDialog"
  title="Delete Cash Transaction"
  description="Are you sure you want to delete this cash transaction? The account balance will be updated accordingly."
  @confirm="destroy"
/>
</AppLayout>
</template>