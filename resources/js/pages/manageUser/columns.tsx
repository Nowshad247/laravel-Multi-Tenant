"use client"

import { UserTable } from "@/types/userTableType"
import { router } from "@inertiajs/react"
import { ColumnDef } from "@tanstack/react-table"
import { Eye } from "lucide-react"

export const columns: ColumnDef<UserTable>[] = [
    {
    accessorKey: "id",
    header: "ID",
    },
    {
    accessorKey: "name",
    header: "Name",
    },
    {
    accessorKey: "email",
    header: "Email",
    },
    {
        accessorKey: "roles",
        header: "Role",
        cell: ({ row }) => row.getValue<string[]>('roles')?.join(', ') ?? '-',
    },
    {
        accessorKey: "email_verified_at",
        header: "Email Verified At",
    },
    {
        accessorKey: "created_at",
        header: "Created At",
    },
{
    id: "actions",
    header: "Actions",
    cell: ({ row }) => {
        const id = row.getValue<number>('id')
        return (
            <div className="flex gap-2">
                <button type="button" onClick={()=>router.get(`/central/admin/show-user/${id}`)} className="px-2 py-1 text-sm text-white bg-black rounded hover:bg-gray-600">
                    <Eye className="w-4 h-4" />
                </button>
            </div>
        );
    },
}
]