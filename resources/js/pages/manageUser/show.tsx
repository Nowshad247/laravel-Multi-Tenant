import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useRef } from 'react';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatDateWithPattern } from '@/lib/utils';

interface UserDetail {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: string[];
    avatar: string | null;
    availableRoles: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage Users', href: '/manage-users' },
    { title: 'User Details', href: '#' },
];

export default function Show({ user }: { user: UserDetail }) {
    const { auth, flash, settings } = usePage<SharedData>().props as unknown as {
        auth: { user: { roles: string[] } };
        flash: { success?: string; error?: string };
        settings: { date_format?: string };
    };

    const isSuperAdmin = auth.user?.roles?.includes('Super Admin');
    const datePattern = (settings && (settings.date_format ?? (settings as any).dateFormat)) || 'Y-m-d';

    // Profile info form
    const profileForm = useForm({
        name: user.name ?? '',
        email: user.email ?? '',
        password: '',
    });

    function submitProfile(e: React.FormEvent) {
        e.preventDefault();
        profileForm.put(`/users/${user.id}`);
    }

    // Role form (Super Admin only)
    const roleForm = useForm({ role: user.roles[0] ?? '' });

    function submitRole(e: React.FormEvent) {
        e.preventDefault();
        roleForm.put(`/users/${user.id}/role`);
    }

    // Avatar upload
    const avatarForm = useForm<{ avatar: File | null }>({ avatar: null });
    const fileInputRef = useRef<HTMLInputElement>(null);

    function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) {
            return;
        }
        avatarForm.setData('avatar', file);
        avatarForm.post(`/users/${user.id}/avatar`, {
            forceFormData: true,
            onSuccess: () => {
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
                avatarForm.reset();
            },
        });
    }

    function handleDeleteAvatar() {
        router.delete(`/users/${user.id}/avatar`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User: ${user.name}`} />

            <div className="space-y-4 p-4">
                {flash.success && (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                )}
                {flash.error && (
                    <Alert variant="destructive">
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-4">
                            <Avatar className="h-16 w-16">
                                <AvatarImage
                                    src={user.avatar ?? undefined}
                                    alt={user.name}
                                    className="object-cover"
                                />
                                <AvatarFallback className="text-lg">
                                    {user.name?.charAt(0)?.toUpperCase()}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <CardTitle>{user.name}</CardTitle>
                                <div className="mt-1 flex flex-wrap gap-1">
                                    {user.roles.map((r) => (
                                        <Badge key={r}>{r}</Badge>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-8">
                        {/* ── Avatar management ── */}
                        <section className="space-y-3">
                            <h3 className="text-sm font-medium">Profile picture</h3>
                            <div className="flex items-center gap-4">
                                <Avatar className="h-20 w-20">
                                    <AvatarImage
                                        src={user.avatar ?? undefined}
                                        alt={user.name}
                                        className="object-cover"
                                    />
                                    <AvatarFallback className="text-lg">
                                        {user.name?.charAt(0)?.toUpperCase()}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="flex flex-col gap-2">
                                    <div className="flex gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            disabled={avatarForm.processing}
                                            onClick={() => fileInputRef.current?.click()}
                                        >
                                            {avatarForm.processing ? 'Uploading…' : 'Upload photo'}
                                        </Button>
                                        {user.avatar && (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                className="text-destructive hover:text-destructive"
                                                onClick={handleDeleteAvatar}
                                            >
                                                Remove
                                            </Button>
                                        )}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        JPG, PNG, GIF or WebP. Max 2 MB.
                                    </p>
                                    {avatarForm.errors.avatar && (
                                        <p className="text-sm text-destructive">
                                            {avatarForm.errors.avatar}
                                        </p>
                                    )}
                                </div>
                            </div>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                className="hidden"
                                onChange={handleFileChange}
                            />
                        </section>

                        {/* ── Role assignment (Super Admin only) ── */}
                        {isSuperAdmin && (
                            <section className="space-y-3">
                                <h3 className="text-sm font-medium">Role</h3>
                                <form onSubmit={submitRole} className="flex items-end gap-3">
                                    <div className="w-56 space-y-1">
                                        <Label htmlFor="role-select">Assign role</Label>
                                        <Select
                                            value={roleForm.data.role}
                                            onValueChange={(value) =>
                                                roleForm.setData('role', value)
                                            }
                                        >
                                            <SelectTrigger id="role-select">
                                                <SelectValue placeholder="Select a role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {user.availableRoles.map((role) => (
                                                    <SelectItem key={role} value={role}>
                                                        {role}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {roleForm.errors.role && (
                                            <p className="text-sm text-destructive">
                                                {roleForm.errors.role}
                                            </p>
                                        )}
                                    </div>
                                    <Button type="submit" size="sm" disabled={roleForm.processing}>
                                        {roleForm.processing ? 'Saving…' : 'Update role'}
                                    </Button>
                                </form>
                            </section>
                        )}

                        {/* ── Profile info ── */}
                        <section className="space-y-3">
                            <h3 className="text-sm font-medium">Profile information</h3>
                            <form
                                onSubmit={submitProfile}
                                className="grid grid-cols-1 gap-4 md:grid-cols-2"
                            >
                                <div className="space-y-1">
                                    <Label>Name</Label>
                                    <Input
                                        value={profileForm.data.name}
                                        onChange={(e) =>
                                            profileForm.setData('name', e.target.value)
                                        }
                                    />
                                    {profileForm.errors.name && (
                                        <p className="text-sm text-destructive">
                                            {profileForm.errors.name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-1">
                                    <Label>Email</Label>
                                    <Input
                                        value={profileForm.data.email}
                                        onChange={(e) =>
                                            profileForm.setData('email', e.target.value)
                                        }
                                    />
                                    {profileForm.errors.email && (
                                        <p className="text-sm text-destructive">
                                            {profileForm.errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-1">
                                    <Label>Password</Label>
                                    <Input
                                        type="password"
                                        placeholder="Leave blank to keep current"
                                        value={profileForm.data.password}
                                        onChange={(e) =>
                                            profileForm.setData('password', e.target.value)
                                        }
                                    />
                                    {profileForm.errors.password && (
                                        <p className="text-sm text-destructive">
                                            {profileForm.errors.password}
                                        </p>
                                    )}
                                </div>

                                <div className="col-span-1 flex gap-2 md:col-span-2">
                                    <Button type="submit" disabled={profileForm.processing}>
                                        {profileForm.processing ? 'Saving…' : 'Save'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => window.history.back()}
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </section>

                        {/* ── Audit ── */}
                        <section className="space-y-2">
                            <h3 className="text-sm font-medium">Audit</h3>
                            <dl className="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                                <dt className="font-medium text-muted-foreground">Created</dt>
                                <dd>{formatDateWithPattern(user.created_at, datePattern)}</dd>
                                <dt className="font-medium text-muted-foreground">Updated</dt>
                                <dd>{formatDateWithPattern(user.updated_at, datePattern)}</dd>
                                <dt className="font-medium text-muted-foreground">Email verified</dt>
                                <dd>
                                    {user.email_verified_at
                                        ? formatDateWithPattern(user.email_verified_at, datePattern)
                                        : 'Not verified'}
                                </dd>
                            </dl>
                        </section>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
