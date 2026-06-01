<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::with('domains')->get()->map(fn (Tenant $tenant) => [
            'id' => $tenant->id,
            'tenancy_db_name' => $tenant->tenancy_db_name,
            'domain' => $tenant->domains->first()?->domain,
            'created_at' => $tenant->created_at?->toDateTimeString(),
        ]);

        return inertia('tenants/index', compact('tenants'));
    }
}
