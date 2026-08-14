# WorkinVirtual Job Board Payments Lite 1.1.3

Version 1.1.3 is the first public Lite release prepared for WordPress.org.

## Highlights

- Complete Stripe Test-mode, one-time listing workflow.
- One configurable basic job package.
- Signed webhook verification and idempotent job-credit granting.
- Basic payment records and environment checks.
- Clean separation from Professional-only live payments and subscriptions.

## Validation

- Installed and activated with WP Job Manager 2.4.5 on a fresh WordPress 7.0 test site.
- WordPress Plugin Check 2.0.0: zero errors.
- WordPress.org automated submission scan: pass.
- Secret scan: no Stripe secret, publishable, or webhook credentials embedded.

## Important

The Lite edition accepts Stripe Test credentials only. Test cards create no real charge. Use the Professional edition for real payments.
