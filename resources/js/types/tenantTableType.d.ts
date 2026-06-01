export interface TenantTable {
    id: string;
    tenancy_db_name: string;
    domain: string | null;
    created_at: string | null;
}
