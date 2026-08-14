# WorkinVirtual Job Board Payments — Lite

The open-source Lite edition of **WorkinVirtual Job Board Payments** provides a complete Stripe Test-mode payment workflow for [WP Job Manager](https://wordpress.org/plugins/wp-job-manager/).

It lets site owners configure one basic one-time job package, open Stripe Test Checkout, verify signed webhook events, grant one standard job credit, and review basic test-payment records. It never processes live payments.

## Requirements

- WordPress 6.2 or newer
- PHP 7.4 or newer
- WP Job Manager
- A Stripe account in Test mode

## Lite features

- Stripe Test mode only
- One basic one-time listing package
- Stripe-hosted Test Checkout
- Signed `checkout.session.completed` webhook handling
- One standard job credit per completed test payment
- Basic payment records and system-status checks
- No WooCommerce dependency

Live payments, subscriptions, featured entitlements, Customer Portal integration, branding controls, and advanced administration are available separately in Professional. Professional code is not included or artificially gated in this repository.

## Installation

For normal installation, download the ZIP from [GitHub Releases](../../releases) and upload it under **WordPress → Plugins → Add New → Upload Plugin**.

1. Activate WP Job Manager.
2. Activate WorkinVirtual Job Board Payments.
3. Open **Job Board Payments → Settings**.
4. enter Stripe Test publishable, secret, and webhook signing keys.
5. Create/connect the package and result pages.
6. Add the displayed webhook endpoint in Stripe Test mode with `checkout.session.completed` enabled.
7. Run a checkout with a Stripe test card.

Full documentation: https://workinvirtual.com/docs/stripe-job-board-payments/

## Development

The repository mirrors the WordPress.org Lite source. Release ZIPs include the pinned Freemius SDK under `vendor/freemius/`. Do not commit credentials or production Stripe identifiers.

Before proposing a change:

1. Test on a fresh supported WordPress installation with WP Job Manager.
2. Run the official Plugin Check plugin.
3. Exercise activation, settings, package creation, checkout validation, webhook rejection/acceptance, credit granting, and uninstall/deactivation behavior.

## Security and support

- Security reports: see [SECURITY.md](SECURITY.md).
- Bugs and feature requests: use [GitHub Issues](../../issues).
- Setup help: see [SUPPORT.md](SUPPORT.md).

## License

GPL-2.0-or-later. See [LICENSE.txt](LICENSE.txt).
