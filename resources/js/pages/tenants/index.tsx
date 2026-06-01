import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { columns as baseColumns } from './columns';
import { formatDateWithPattern } from '@/lib/utils';
import { TenantTable } from '@/types/tenantTableType';
import { dashboard } from '../../routes/central';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Company Manager',
        href: dashboard().url,
    },
];

export default function Tenants({ tenants }: { tenants: TenantTable[] }) {
    const { settings } = usePage().props as unknown as { settings: { date_format?: string } };

    const datePattern = settings?.date_format ?? 'Y-m-d';

    const columns = baseColumns.map((col) => {
        if ('accessorKey' in col && col.accessorKey === 'created_at') {
            return {
                ...col,
                cell: ({ getValue }: { getValue: () => string | null }) =>
                    formatDateWithPattern(getValue(), datePattern),
            } as typeof col;
        }

        return col;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company Manager" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <DataTable columns={columns} data={tenants} />
            </div>
        </AppLayout>
    );
}
