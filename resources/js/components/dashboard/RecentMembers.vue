<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

import { Badge } from "@/components/ui/badge";

interface Member {
    id: number;
    member_code: string;
    name: string;
    status: string;
    join_date: string;
}

defineProps<{
    members: Member[];
}>();

const badgeVariant = (status: string) => {
    switch (status) {
        case "active":
            return "default";

        case "inactive":
            return "secondary";

        case "suspended":
            return "destructive";

        default:
            return "outline";
    }
};
</script>

<template>
    <Card>

        <CardHeader>
            <CardTitle>
                New Members
            </CardTitle>
        </CardHeader>

        <CardContent>

            <Table>

                <TableHeader>

                    <TableRow>
                        <TableHead>Member</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Join Date</TableHead>
                    </TableRow>

                </TableHeader>

                <TableBody>

                    <TableRow
                        v-for="member in members"
                        :key="member.id"
                    >

                        <TableCell>

                            <div>

                                <p class="font-medium">
                                    {{ member.name }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    {{ member.member_code }}
                                </p>

                            </div>

                        </TableCell>

                        <TableCell>

                            <Badge
                                :variant="badgeVariant(member.status)"
                            >
                                {{ member.status }}
                            </Badge>

                        </TableCell>

                        <TableCell>

                            {{ member.join_date }}

                        </TableCell>

                    </TableRow>

                    <TableRow
                        v-if="!members.length"
                    >
                        <TableCell
                            colspan="3"
                            class="text-center text-muted-foreground"
                        >
                            No members found.
                        </TableCell>
                    </TableRow>

                </TableBody>

            </Table>

        </CardContent>

    </Card>
</template>