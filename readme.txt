=== WorkinVirtual Job Board Payments – Stripe Test Checkout ===
Contributors: bstn001
Tags: wp job manager, stripe, job board, test payments
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Test Stripe job-listing checkout and signed webhooks with one basic WP Job Manager package. No WooCommerce required.

== Description ==

Stripe Job Board Payments adds a useful Stripe Test-mode payment workflow to WP Job Manager.

The free edition lets an administrator create one standard one-time listing package, send logged-in employers to Stripe Test Checkout, record completed test payments through a signed webhook, grant one job-listing credit, and inspect basic status and payment records.

The free edition never accepts live payments. Stripe test cards create no real charge.

= Free features =

* WP Job Manager integration
* Stripe Test mode only
* One basic one-time job-listing package
* Stripe-hosted Test Checkout
* Signed webhook verification
* One standard job credit per completed test payment
* Basic payment records
* Basic System Status checks
* Package and payment-result shortcodes
* Documentation and support links

= Professional =

Stripe Job Board Payments Professional adds Live-mode payment acceptance, recurring employer subscriptions, featured-listing entitlements, recurring job credits, Stripe Customer Portal integration, advanced payment and subscription administration, branding controls, and lifecycle automation.

Learn more: https://workinvirtual.com/products/stripe-job-board-payments/

Documentation: https://workinvirtual.com/docs/stripe-job-board-payments/

== Installation ==

1. Install and activate WP Job Manager.
2. Upload the plugin ZIP from Plugins > Add New, or install it from WordPress.org.
3. Activate Stripe Job Board Payments.
4. Open Job Board Payments > Settings.
5. Add your Stripe Test Publishable Key, Test Secret Key, and Test webhook signing secret.
6. Create or select the package and payment-result pages.
7. Configure Stripe to send `checkout.session.completed` to the webhook endpoint shown in Settings.
8. Open the package page while logged in and complete checkout with a Stripe test card.

The free edition accepts only keys beginning with `pk_test_`, `sk_test_`, and `whsec_`. It cannot process Live-mode payments.

== Frequently Asked Questions ==

= Does the free edition accept real payments? =

No. The free edition is intentionally limited to Stripe Test mode. Use it to configure and validate the workflow without moving real money.

= Is the free edition functional without a paid license? =

Yes. It provides one complete Test-mode, one-time listing workflow and basic payment records without a purchase.

= Does it require WooCommerce? =

No. It integrates directly with WP Job Manager and Stripe Checkout.

= What Stripe webhook event is required? =

Enable `checkout.session.completed` for the webhook endpoint displayed under Job Board Payments > Settings.

= How is a job credit granted? =

After a correctly signed Test-mode Checkout event matches a pending order, the plugin grants the employer one standard listing credit. The credit is consumed when that user submits a new WP Job Manager listing.

= What is available in Professional? =

Professional provides Live payments, subscriptions, featured entitlements, recurring credits, Customer Portal access, advanced administration, branding controls, and additional lifecycle automation.

= Where can I get help? =

Read https://workinvirtual.com/docs/stripe-job-board-payments/ or email support@workinvirtual.com.

== Screenshots ==

1. Free-edition dashboard and setup checklist.
2. Stripe Test settings and webhook endpoint.
3. Basic one-time job-listing package editor.
4. Public Test-mode package card.
5. Basic Test payment records.
6. System Status checks.

== External services ==

This plugin connects to Stripe only when an administrator configures Stripe Test credentials or a logged-in employer starts Test Checkout. Checkout data required to create the payment session is sent to Stripe, and Stripe sends signed webhook event data back to the site. Stripe services are provided under the Stripe Terms of Service (https://stripe.com/legal) and Privacy Policy (https://stripe.com/privacy).

This plugin includes the Freemius SDK for optional account connection, usage opt-in, update, support, and upgrade functionality. Freemius does not receive data unless the administrator chooses the relevant opt-in or account action. Freemius services are provided under its Terms (https://freemius.com/terms/) and Privacy Policy (https://freemius.com/privacy/).

No externally hosted executable code is loaded by this plugin.

== Privacy ==

The plugin stores package configuration, Test payment records, Stripe Test identifiers, and job-credit metadata in the site's WordPress database. Stripe credentials are stored in WordPress options and are accessible only to administrators with the `manage_options` capability. Site owners should document their own Stripe and payment-data practices in their privacy notice.

== Upgrade Notice ==

= 1.1.3 =

Initial WordPress.org free edition with a functional Stripe Test-mode one-time listing workflow.

== Changelog ==

= 1.1.3 =

* Add a functional free Stripe Test-mode workflow.
* Add one basic one-time listing package.
* Add signed webhook processing and job-credit granting.
* Add basic payments, settings, System Status, and documentation access.
* Keep Live payments and advanced commercial features in Professional.
