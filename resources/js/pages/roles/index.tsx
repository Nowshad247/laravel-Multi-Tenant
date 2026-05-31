import { useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Pencil, Plus, Trash2 } from 'lucide-react';

interface Role {
    id: number;
    name: string;
    permissions: string[];
}

interface PageProps {
    flash: { message?: string; error?: string };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage Permissions', href: '#' },
];

export default function RolesIndex({
    roles,
    permissions,
}: {
    roles: Role[];
    permissions: string[];
}) {
    const { flash } = usePage().props as unknown as PageProps;

    const createForm = useForm({ name: '', permissions: [] as string[] });
    const [createOpen, setCreateOpen] = useState(false);

    const editForm = useForm({ name: '', permissions: [] as string[] });
    const [editOpen, setEditOpen] = useState(false);
    const [editingRole, setEditingRole] = useState<Role | null>(null);

    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deletingRole, setDeletingRole] = useState<Role | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    function openEdit(role: Role) {
        setEditingRole(role);
        editForm.setData({ name: role.name, permissions: [...role.permissions] });
        setEditOpen(true);
    }

    function openDelete(role: Role) {
        setDeletingRole(role);
        setDeleteOpen(true);
    }

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post('/role/store', {
            onSuccess: () => {
                setCreateOpen(false);
                createForm.reset();
            },
        });
    }

    function handleEdit(e: React.FormEvent) {
        e.preventDefault();
        if (!editingRole) {
            return;
        }
        editForm.put(`/role/update/${editingRole.id}`, {
            onSuccess: () => setEditOpen(false),
        });
    }

    function handleDelete() {
        if (!deletingRole) {
            return;
        }
        setIsDeleting(true);
        router.delete(`/role/delete/${deletingRole.id}`, {
            onSuccess: () => {
                setDeleteOpen(false);
                setDeletingRole(null);
            },
            onFinish: () => setIsDeleting(false),
        });
    }

    function toggleCreatePermission(perm: string) {
        const current = createForm.data.permissions;
        createForm.setData(
            'permissions',
            current.includes(perm) ? current.filter((p) => p !== perm) : [...current, perm],
        );
    }

    function toggleEditPermission(perm: string) {
        const current = editForm.data.permissions;
        editForm.setData(
            'permissions',
            current.includes(perm) ? current.filter((p) => p !== perm) : [...current, perm],
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Permissions" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {flash.message && (
                    <Alert>
                        <AlertDescription>{flash.message}</AlertDescription>
                    </Alert>
                )}
                {flash.error && (
                    <Alert variant="destructive">
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Roles & Permissions</h1>
                    <Button onClick={() => setCreateOpen(true)}>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Role
                    </Button>
                </div>

                <div className="overflow-hidden rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12">ID</TableHead>
                                <TableHead className="w-40">Role Name</TableHead>
                                <TableHead>Permissions</TableHead>
                                <TableHead className="w-24">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {roles.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="text-muted-foreground h-24 text-center"
                                    >
                                        No roles found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                roles.map((role) => (
                                    <TableRow key={role.id}>
                                        <TableCell>{role.id}</TableCell>
                                        <TableCell className="font-medium">{role.name}</TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {role.permissions.length === 0 ? (
                                                    <span className="text-muted-foreground text-sm">
                                                        No permissions
                                                    </span>
                                                ) : (
                                                    role.permissions.map((perm) => (
                                                        <Badge key={perm} variant="secondary">
                                                            {perm}
                                                        </Badge>
                                                    ))
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-1">
                                                <button
                                                    type="button"
                                                    onClick={() => openEdit(role)}
                                                    className="hover:bg-accent rounded p-1.5"
                                                    title="Edit role"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </button>
                                                {role.name !== 'Super Admin' && (
                                                    <button
                                                        type="button"
                                                        onClick={() => openDelete(role)}
                                                        className="text-destructive hover:bg-destructive/10 rounded p-1.5"
                                                        title="Delete role"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>

            {/* Create Role Dialog */}
            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Create Role</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreate} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="create-name">Role Name</Label>
                            <Input
                                id="create-name"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                                placeholder="e.g. Editor"
                            />
                            {createForm.errors.name && (
                                <p className="text-destructive text-sm">{createForm.errors.name}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label>Permissions</Label>
                            <div className="max-h-56 space-y-2 overflow-y-auto rounded border p-3">
                                {permissions.map((perm) => (
                                    <div key={perm} className="flex items-center gap-2">
                                        <Checkbox
                                            id={`create-perm-${perm}`}
                                            checked={createForm.data.permissions.includes(perm)}
                                            onCheckedChange={() => toggleCreatePermission(perm)}
                                        />
                                        <Label
                                            htmlFor={`create-perm-${perm}`}
                                            className="cursor-pointer font-normal"
                                        >
                                            {perm}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setCreateOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {createForm.processing ? 'Creating…' : 'Create Role'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Edit Role Dialog */}
            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Edit Role</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleEdit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-name">Role Name</Label>
                            <Input
                                id="edit-name"
                                value={editForm.data.name}
                                onChange={(e) => editForm.setData('name', e.target.value)}
                                disabled={editingRole?.name === 'Super Admin'}
                            />
                            {editForm.errors.name && (
                                <p className="text-destructive text-sm">{editForm.errors.name}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label>Permissions</Label>
                            <div className="max-h-56 space-y-2 overflow-y-auto rounded border p-3">
                                {permissions.map((perm) => (
                                    <div key={perm} className="flex items-center gap-2">
                                        <Checkbox
                                            id={`edit-perm-${perm}`}
                                            checked={editForm.data.permissions.includes(perm)}
                                            onCheckedChange={() => toggleEditPermission(perm)}
                                            disabled={editingRole?.name === 'Super Admin'}
                                        />
                                        <Label
                                            htmlFor={`edit-perm-${perm}`}
                                            className="cursor-pointer font-normal"
                                        >
                                            {perm}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setEditOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={
                                    editForm.processing || editingRole?.name === 'Super Admin'
                                }
                            >
                                {editForm.processing ? 'Saving…' : 'Save Changes'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Role</DialogTitle>
                    </DialogHeader>
                    <p className="text-muted-foreground text-sm">
                        Are you sure you want to delete the{' '}
                        <strong className="text-foreground">{deletingRole?.name}</strong> role?
                        This action cannot be undone.
                    </p>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setDeleteOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={isDeleting}
                        >
                            {isDeleting ? 'Deleting…' : 'Delete Role'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
