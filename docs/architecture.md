# Architecture

The Lite plugin uses WordPress hooks and a small set of purpose-specific classes:

- `WIVJA_Admin`: Lite settings, package, payment, help, and status screens.
- `WIVJA_Stripe`: Stripe Test Checkout session requests.
- `WIVJA_Webhook`: signed event verification and completion processing.
- `WIVJA_DB`: plugin-owned packages, orders, and log tables.
- `WIVJA_WPJM`: job-credit eligibility and single-consumption integration.
- `WIVJA_Shortcodes`: public package and payment-result output.

All Lite checkout execution is Test-mode only. A verified pending order changes to paid exactly once before credit is granted. The Professional edition is distributed separately and is not hidden inside the Lite codebase.
