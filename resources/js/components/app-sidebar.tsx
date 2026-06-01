import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import { dashboard } from '@/routes/central';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { Boxes, LayoutGrid, ShieldCheck , SlidersHorizontal,UsersRound} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Company',
        href: '/admin/tenant/dashboard',
        icon: Boxes,
    },
    {
        title: 'Manage Users',
        href: '/manage-users',
        icon: UsersRound,
    },
    
];

const footerNavItems: NavItem[] = [
    
];

export function AppSidebar() {
    const { auth } = usePage().props as unknown as { auth: { user: { roles: string[] } | null } };
    const isSuperAdminOrEditor = auth.user?.roles?.some((r: string) =>
        ['Super Admin', 'Editor'].includes(r),
    );
    const isSuperAdmin = auth.user?.roles?.includes('Super Admin');

    const navItems: NavItem[] = [
        ...mainNavItems,
        ...(isSuperAdmin
            ? [
                  {
                      title: 'Manage Permissions',
                      href: '/userspermissions',
                      icon: ShieldCheck,
                  } satisfies NavItem,
                  {
                      title: 'Configuration',
                      href: '/settings',
                      icon: SlidersHorizontal,
                  } satisfies NavItem,
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard().url} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={isSuperAdminOrEditor ? navItems : []} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
