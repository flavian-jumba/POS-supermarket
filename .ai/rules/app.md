---
paths:
  - 'app/**'
---

# App

## Use Workspace for current tenant and POS context
Custom app routes and POS must use App\Support\Workspace session keys for current organization/branch/register/session. Filament tenant middleware synchronizes /admin/{organization} into Workspace; do not introduce separate tenant session keys or unscoped register/product lookups.

## Resolve M-Pesa credentials from tenant integration
M-Pesa/Daraja code must resolve credentials from the current Organization's MpesaIntegration, never from global env credentials or client-submitted organization IDs. OAuth tokens are cached per mpesa_token:{integration_id}:{environment}; callbacks must resolve by CheckoutRequestID and remain idempotent.
