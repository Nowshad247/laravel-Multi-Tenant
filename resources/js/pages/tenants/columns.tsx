"use client"

import { TenantTable } from "@/types/tenantTableType"
import { ColumnDef } from "@tanstack/react-table"

export const columns: ColumnDef<TenantTable>[] = [
    {
        accessorKey: "id",
        header: "ID",
    },
    {
        accessorKey: "tenancy_db_name",
        header: "Database",
    },
    {
        accessorKey: "domain",
        header: "Domain",
        cell: ({ getValue }) => getValue<string | null>() ?? "-",
    },
    {
        accessorKey: "created_at",
        header: "Created At",
    },
]
