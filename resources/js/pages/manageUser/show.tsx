import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { usePage } from '@inertiajs/react';
import { formatDateWithPattern } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage Users', href: '/manage-users' },
    { title: 'User Details', href: '#' },
];

export default function Show() {
    const { props } = usePage();
    const user = (props as any).user as any;
    const { settings } = props as any;
    const datePattern = (settings && (settings.date_format ?? settings.dateFormat)) || 'Y-m-d';

    const { data, setData, put, processing, errors } = useForm({
        name: user.name ?? '',
        email: user.email ?? '',
        password: '',
        avatar: user.avatar ?? '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        put(`/users/${user.id}`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User: ${user.name}`} />

            <div className="p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-4">
                            <Avatar className="h-16 w-16">
                                {user.avatar ? (
                                    <AvatarImage src={user.avatar} alt={user.name} />
                                ) : (
                                    <AvatarFallback>{user.name?.charAt(0)}</AvatarFallback>
                                )}
                            </Avatar>
                            <div>
                                <CardTitle>{user.name}</CardTitle>
                                <div className="mt-1">
                                    {user.roles?.map((r: string) => (
                                        <Badge key={r} className="mr-2">{r}</Badge>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label>Name</Label>
                                <Input value={data.name} onChange={(e)=>setData('name', e.target.value)} />
                                {errors.name && <div className="text-destructive">{errors.name}</div>}
                            </div>

                            <div>
                                <Label>Email</Label>
                                <Input value={data.email} onChange={(e)=>setData('email', e.target.value)} />
                                {errors.email && <div className="text-destructive">{errors.email}</div>}
                            </div>

                            <div>
                                <Label>Password (leave blank to keep current)</Label>
                                <Input type="password" value={data.password} onChange={(e)=>setData('password', e.target.value)} />
                                {errors.password && <div className="text-destructive">{errors.password}</div>}
                            </div>

                            <div>
                                <Label>Avatar (URL)</Label>
                                <Input value={data.avatar} onChange={(e)=>setData('avatar', e.target.value)} />
                                {errors.avatar && <div className="text-destructive">{errors.avatar}</div>}
                            </div>

                            <div className="col-span-1 md:col-span-2 flex gap-2">
                                <Button type="submit" disabled={processing}>Save</Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>Cancel</Button>
                            </div>
                        </form>

                        <div className="mt-6">
                            <h3 className="text-lg font-medium">Audit</h3>
                            <table className="w-full mt-2 table-fixed">
                                <tbody>
                                    <tr>
                                        <td className="font-medium">Created At</td>
                                        <td>{formatDateWithPattern(user.created_at, datePattern)}</td>
                                    </tr>
                                    <tr>
                                        <td className="font-medium">Updated At</td>
                                        <td>{formatDateWithPattern(user.updated_at, datePattern)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
