import ErrorPage from '@/components/ErrorPage';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import {
  Alert,
  AlertDescription,
  AlertTitle,
} from "@/components/ui/alert"
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Basic form',
        href: dashboard().url,
    },
    
];

export default function Dashboard() {
    const { data, setData, post, processing, errors } = useForm({
        name: 'Nowshad',
        email: 'mdnowsahd9@gmail.com',
    });
const handeSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/basic-submit');
}
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Basic form" />
            {Object.keys(errors).length > 0 && (
                <Alert>
                <AlertTitle>Errors</AlertTitle>
                <AlertDescription>
                    {Object.values(errors).map((error, index) => (
                        <li key={index}>{error}</li>
                    ))}
                </AlertDescription>
                </Alert>
            )}
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <form className='y-4' onSubmit={handeSubmit}>
                    <div className="input w-8/12 mb-4">
                        <Label htmlFor="name">Name</Label>
                        <Input type="text" name="name" value={data.name}  onChange={(e)=>setData('name',e.target.value)}/>
                    </div>
                    <div className="input w-8/12 mb-4">
                        <Label htmlFor="email">Email</Label>
                        <Input type="email" name="email" value={data.email} onChange={(e)=>setData('email',e.target.value)}/>
                    </div>
                    <Button type="submit">Add Student </Button>  
                </form>

            </div>
        </AppLayout>
    );
}
