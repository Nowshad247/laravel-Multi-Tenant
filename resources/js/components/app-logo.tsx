import AppLogoIcon from './app-logo-icon';
import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const settings = (usePage().props as any).settings ?? {};
    const logo = settings.site_logo as string | undefined;
    const siteName = settings.site_name ?? 'Skills Development Centre';

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                {logo ? (
                    <img
                        src={logo}
                        alt={siteName}
                        className="h-6 w-6 rounded object-contain"
                    />
                ) : (
                    <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                )}
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {siteName}
                </span>
            </div>
        </>
    );
}
