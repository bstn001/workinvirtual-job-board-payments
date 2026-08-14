# Development and testing

## Minimum smoke matrix

1. Activate without WP Job Manager and confirm the dependency is reported.
2. Activate with WP Job Manager and create the generated pages.
3. Confirm only Test key fields and one-time package controls exist.
4. Reject missing or malformed Test keys.
5. Reject invalid webhook signatures, stale timestamps, live-mode events, mismatched orders, and duplicate events.
6. Confirm one verified completion grants one credit.
7. Confirm the first eligible job consumes that credit only once.
8. Confirm no live, subscription, featured, or Customer Portal controls appear.
9. Run WordPress Plugin Check with error and warning checks enabled.

Use synthetic data only. Never place production Stripe keys in a development database, fixture, log, screenshot, issue, or commit.
