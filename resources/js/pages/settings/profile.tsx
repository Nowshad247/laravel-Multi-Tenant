import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { send } from '@/routes/verification';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useRef } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/hooks/use-initials.tsx';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit } from '@/routes/profile';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();
    const fileInputRef = useRef<HTMLInputElement>(null);

    const avatarForm = useForm<{ avatar: File | null }>({ avatar: null });

    function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) {
            return;
        }
        avatarForm.setData('avatar', file);
        avatarForm.post('/settings/avatar', {
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
        router.delete('/settings/avatar');
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    {/* Avatar section */}
                    <div className="space-y-4">
                        <HeadingSmall
                            title="Profile picture"
                            description="Upload a photo to personalise your account"
                        />

                        <div className="flex items-center gap-4">
                            <Avatar className="h-20 w-20">
                                <AvatarImage
                                    src={auth.user.avatar as string | undefined}
                                    alt={auth.user.name}
                                    className="object-cover"
                                />
                                <AvatarFallback className="text-lg">
                                    {getInitials(auth.user.name)}
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

                                    {auth.user.avatar && (
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

                                {status === 'avatar-updated' && (
                                    <p className="text-sm text-green-600">
                                        Profile picture updated.
                                    </p>
                                )}
                                {status === 'avatar-deleted' && (
                                    <p className="text-sm text-muted-foreground">
                                        Profile picture removed.
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
                    </div>

                    {/* Profile info section */}
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Profile information"
                            description="Update your name and email address"
                        />

                        <Form
                            {...ProfileController.update.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            className="space-y-6"
                        >
                            {({ processing, recentlySuccessful, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Name</Label>

                                        <Input
                                            id="name"
                                            className="mt-1 block w-full"
                                            defaultValue={auth.user.name}
                                            name="name"
                                            required
                                            autoComplete="name"
                                            placeholder="Full name"
                                        />

                                        <InputError className="mt-2" message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email address</Label>

                                        <Input
                                            id="email"
                                            type="email"
                                            className="mt-1 block w-full"
                                            defaultValue={auth.user.email}
                                            name="email"
                                            required
                                            autoComplete="username"
                                            placeholder="Email address"
                                        />

                                        <InputError className="mt-2" message={errors.email} />
                                    </div>

                                    {mustVerifyEmail && auth.user.email_verified_at === null && (
                                        <div>
                                            <p className="-mt-4 text-sm text-muted-foreground">
                                                Your email address is unverified.{' '}
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                    className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                                >
                                                    Click here to resend the verification email.
                                                </Link>
                                            </p>

                                            {status === 'verification-link-sent' && (
                                                <div className="mt-2 text-sm font-medium text-green-600">
                                                    A new verification link has been sent to your
                                                    email address.
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    <div className="flex items-center gap-4">
                                        <Button
                                            disabled={processing}
                                            data-test="update-profile-button"
                                        >
                                            Save
                                        </Button>

                                        <Transition
                                            show={recentlySuccessful}
                                            enter="transition ease-in-out"
                                            enterFrom="opacity-0"
                                            leave="transition ease-in-out"
                                            leaveTo="opacity-0"
                                        >
                                            <p className="text-sm text-neutral-600">Saved</p>
                                        </Transition>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
