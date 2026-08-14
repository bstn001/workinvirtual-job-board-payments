# Contributing

Thank you for helping improve WorkinVirtual Job Board Payments Lite.

## Before opening an issue

- Confirm WP Job Manager is active.
- Reproduce the problem with the latest Lite release.
- Use Stripe Test mode only.
- Remove API keys, webhook secrets, customer details, email addresses, and payment identifiers from screenshots and logs.

## Pull requests

Keep each pull request focused. Explain the behavior changed, security implications, and manual test coverage. New payment or webhook code must use WordPress capability checks, nonces where applicable, sanitization, escaping, prepared SQL, signed webhook verification, and idempotent state transitions.

Run the official Plugin Check plugin and report its result. Do not add Professional source, live credentials, tracking without explicit opt-in, remote executable code, or dependencies with an incompatible license.

By contributing, you agree that your contribution is licensed under GPL-2.0-or-later.
