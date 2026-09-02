# ARCH-001: Two-app split — storefront vs. core platform

## Date
2026-09-02

## Scope
Project-wide architecture context.

## Architecture
The Presence Platform product family intentionally consists of two independent
applications:

1. **Marketplace storefront** (THIS repository, `B2B-Marketplace`)
   - Laravel 13 + Blade, SQLite, no auth, no payments.
   - Single-vendor marketing/sales site: five pages, one contact table.
   - Its job is to describe the product and collect demo requests.

2. **Core Presence Platform** (SEPARATE repository, owned by the platform team)
   - The actual product: ESP32 NFC readers, event ingestion backend,
     dashboards, CV classifier, NL query interface.
   - Hardware/backend work that is currently blocked and out of scope here.

## Decoupling rules
- The storefront must build, run, and demo with zero dependency on the platform's
  backend, API, hardware, or event ingestion. It contains no platform client code.
- The platform build must never depend on this storefront.
- The ONLY intended relationship: the storefront describes the product and routes
  interested organizations to a "Request a demo" flow (the contact form).

## Rationale
Marketing surface and product infrastructure have different release cadences,
risk profiles, and owners. Coupling them would let a storefront bug block the
product build (and vice versa).

## Verified
- No references to any platform API/host/SDK exist in this repository (routes,
  controllers, config, lang files checked — see RUN-2026-09-02 verification).
