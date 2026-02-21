# AI Usage Disclosure

## AI Tool Used

- GPT-based coding assistant in terminal workflow.

## What AI Generated Initially

- Initial plugin folder/file skeleton.
- Preliminary conditional fee logic draft.
- Initial class decomposition ideas for pricing and analytics.

## What Was Manually Improved / Refactored

1. Converted flat procedural drafts into modular OOP services.
2. Added explicit contracts (`HookableInterface`, `PricingRuleInterface`).
3. Implemented lightweight dependency container and bootstrap orchestrator.
4. Added secure logging sanitation for PII-like fields.
5. Added DB repository with activation-time table creation via `dbDelta`.
6. Added async execution abstraction with Action Scheduler + WP-Cron fallback.
7. Added settings page sanitization and typed value normalization.
8. Added uninstall behavior and complete documentation package.

## What AI Got Wrong Initially

- Suggested fee logic without complete edge checks for admin/AJAX cart lifecycle.
- Proposed simplistic pricing adjustments with risk of repeated compounding.
- Included insufficient sanitization for settings and logs.
- Did not include robust extensibility and migration-focused architecture details.

## Security and Architectural Improvements Added

- Sanitized all admin inputs by field type.
- Redacted sensitive logging payloads.
- Added explicit ABSPATH guards and safe DB insert formats.
- Introduced separation of concerns and dependency injection.
- Added extension filters and interface-based pricing rules.

## Final Responsibility

Final architecture, security hardening, and production-readiness adjustments were manually validated and applied.

