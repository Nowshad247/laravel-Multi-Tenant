import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';

import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { DataTable } from '@/components/data-table';
import { columns as baseColumns } from './columns';
import { formatDateWithPattern } from '@/lib/utils';
import { UserTable } from '@/types/userTableType';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Users',
        href: '#',
    },
];


export default function ManageUsers({ users }: { users: UserTable[] }) {
      const { flash, settings } = usePage().props as unknown as { flash: { message?: string }, settings: { date_format?: string } }

      const datePattern = (settings && (settings.date_format ?? settings.dateFormat)) || 'Y-m-d';

      const columns = baseColumns.map((col) => {
          if (col.accessorKey === 'created_at' || col.accessorKey === 'email_verified_at') {
              return {
                  ...col,
                  cell: ({ getValue }: any) => formatDateWithPattern(getValue() as string | null, datePattern),
              } as typeof col;
          }

          return col;
      });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            {flash.message && (
                <Alert>
                    <AlertTitle>{flash.message}</AlertTitle>
                    <AlertDescription>
                        {flash.message}
                    </AlertDescription>
                    </Alert>
             )}
            <Head title="Manage Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
             <DataTable columns={columns} data={users} />
            </div>
        </AppLayout>
    );
}
