# Vexi ERP SaaS Product

The platform is structured as a multi-tenant product:

- Each company is a tenant with isolated users, settings, modules, requirements, and subscription.
- `X-Tenant-ID` plus the authenticated user's tenant membership resolves the active company context.
- Plans: Starter, Growth, Enterprise. Each tenant can enable only the modules/features included in its plan.
- Client-specific requirements are tracked with priority, acceptance criteria, and delivery status.
- All business APIs can run under the tenant middleware. Existing module tables already carry `tenant_id` where applicable; remaining legacy tables should be backfilled before production migration.
- Module configuration supports editions and feature overrides per client, so the same codebase can sell different packages without forks.

## Critical production step

Before go-live, backfill `tenant_id` on legacy records, add tenant-scoped policies/global scopes to every model, and test cross-tenant access with automated security tests. Do not ship a multi-tenant ERP without this gate.
