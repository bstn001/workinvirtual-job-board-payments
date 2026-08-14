# Changelog

All notable changes to the Lite edition are documented here.

## [1.1.3] — 2026-08-14

### Added

- Functional Stripe Test-mode checkout for one basic one-time job package.
- Signed `checkout.session.completed` webhook processing.
- One standard WP Job Manager credit after a verified test payment.
- Basic payments, settings, System Status, package, and payment-result screens.
- WordPress.org-compatible free/commercial architecture and external-service disclosure.
- Public documentation, contribution guidance, security policy, and issue templates.

### Security

- Enforced Stripe Test keys and HTTPS Checkout URLs.
- Restricted checkout redirects to Stripe Checkout hosts.
- Added nonce, capability, signature, event-mode, order, package, and payment-status checks.
- Made webhook credit granting idempotent.
- Removed live-payment, subscription, featured-entitlement, and Customer Portal implementation from the Lite build.

### Compliance

- Aligned the directory slug and text domain to `workinvirtual-job-board-payments`.
- Passed WordPress Plugin Check with zero errors.
- Passed the WordPress.org readme validator.

[1.1.3]: https://github.com/bstn001/workinvirtual-job-board-payments/releases/tag/v1.1.3
