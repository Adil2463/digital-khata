# Digital Khata — Roadmap

Delivery happens in phases. Each phase (and each Phase 2 part) starts only after the owner explicitly approves it. Every phase ends with `composer generate-and-cleanup`, `composer test`, `npm run test:unit`, a client/SSR build when the frontend changed, updated docs and `AGENTS.md`, and a Manual QA pass. Run every gate from `D:\digital-khata` (uppercase drive letter). No phase ships partial features.

| #   | Phase                                      | Status                        |
| --- | ------------------------------------------ | ----------------------------- |
| 1   | Foundation and Architecture                | Complete, approved            |
| 2A  | Shop foundation and secure installation    | Complete, approved            |
| 2B  | Owner and staff management                 | Implemented — awaiting review |
| 2C  | Shop settings and app shell                | Proposed (below)              |
| 3   | Customer Management                        | Not started                   |
| 4   | Financial Ledger Engine                    | Not started                   |
| 5   | Quick and Detailed Udhaar                  | Not started                   |
| 6   | Payment Collection                         | Not started                   |
| 7   | Customer Khata and Statements              | Not started                   |
| 8   | Dashboard and Reports                      | Not started                   |
| 9   | Backup and Data Safety                     | Not started                   |
| 10  | Production Readiness and Real-Shop Testing | Not started                   |

## Phase 2A — Shop Foundation And Secure Installation (Complete)

- `shops` table and `users.shop_id` (nullable, `RESTRICT`), `App\Models\Shop`, `User::shop()`.
- `php artisan khata:install`: creates the single shop and a verified owner from hidden password prompts. It refuses a second run, refuses `--no-interaction`, validates input (`Password::defaults()`, unique login, real timezone), and attaches all existing accounts to the shop. It detects accounts still using the starter kit's published demo passwords and, after confirmation, locks them (random unknown password, remember token rotated, Sanctum tokens and database sessions revoked) without deleting them. Declining cancels with no changes. Output and the audit log never contain passwords.
- `php artisan khata:user:reset-password <email>`: offline recovery with the same credential revocation.
- Public registration removed: routes, controller, request, DTO, page, form schema, navigation links, tests.
- Email forgot/reset password removed: routes, controllers, requests, DTOs, pages, form schemas, the `ResetPassword` URL hook, the login link (replaced by "Ask the shop owner to reset it"), tests.
- Demo seeding: `APP_SEED_USERS` defaults to `false`, is pinned `false` in `phpunit.xml`, and never seeds in production.

Known limits at the end of 2A were resolved in 2B.

## Phase 2B — Owner And Staff Management (Implemented)

- `users.deactivated_at` (migration `2026_10_17_000001_add_deactivated_at_to_users_table`). An account can sign in only when it belongs to a shop and is not deactivated (`User::canSignIn()`).
- Staff screen (`/app/admin/users`, owner-only): add staff or another owner, edit name, login, role, or password, deactivate, reactivate. There is no deletion anywhere (web, API, or Settings).
- New accounts join the creating owner's shop; a client-sent `shop_id` is ignored. Owner-created and owner-edited accounts are verified, so staff never see the email verification screen.
- Logins are trimmed and lower-cased and must be unique. Passwords need at least 10 characters with letters and numbers, confirmed, everywhere (`Password::defaults()`).
- Changing a login or password revokes that account's remember token, API tokens, and database sessions; owners editing themselves stay signed in on the current device. Deactivation revokes everything.
- `EnsureAccountCanSignIn` checks every authenticated web and API request, so a deactivated account loses access on its next click, even with another session driver or a surviving session row.
- Owners cannot deactivate or demote themselves; a shop always keeps one active owner (enforced under row locks). Other shops' accounts return 404.
- Remember Me removed. Sessions expire after 30 idle minutes (`SESSION_LIFETIME`); an expired form submit returns to login with a message.
- Login throttling: 5 attempts per login and IP (existing) plus 20 per minute per IP across logins (new `login` limiter).
- Account self-deletion removed from Settings → Profile. Changing your own login or password in Settings signs out other devices only.
- Management notifications reach only active owners of the same shop. Realtime actions: `create`, `update`, `deactivate`, `reactivate`.
- Demo seeding no longer uses the evolving `User` model, so seeded demo accounts exist without a shop and cannot sign in until `khata:install` attaches and locks them.

## Phase 2C — Shop Settings And App Shell (Proposed)

1. `CurrentShop` resolver, `ShopClock::today()`, `SharedShopData` page prop (shop name for authenticated users).
2. Owner-only shop settings page (`GET|PUT /app/shop/settings`, `manage-shop` gate): name, phone, address, receipt footer, timezone.
3. Header and page titles show the shop name; login branding shows Digital Khata.
4. Root `/` behaviour (decision O3).
5. Tests and docs.

## Later Phases

**3. Customer Management.** `Customers` module: `customers` and `activity_logs` migrations; create, edit, search, list, archive, restore, and delete-if-empty; phone normalization; duplicate warnings; generated codes from the id; optional photo (private disk, WebP re-encode, authorized streaming, replace/remove, pruner); `BaseCustomerAvatar`; keyboard search. Exit: W1, W2, W11, and W12 pass automated and manual QA, including every photo test in [Architecture §7](DIGITAL_KHATA_ARCHITECTURE.md#7-testing-strategy).

**4. Financial Ledger Engine.** `Ledger` module core: `ledger_entries` with CHECKs; `Money`, `config/khata.php` limits, `LedgerCommands::post`; opening balance (W3, including on the customer form for the owner); reversal and correction engine; idempotency; warnings with one confirmation flag; L22 backdating; corrections notice and as-recorded queries (L53, L64); `khata:ledger:verify`; `composer test:mysql`, including immutability and restricted-grant tests (S3); accuracy and performance tests (S1). Exit: every rule L1–L71 has a named test and the MySQL group passes locally.

**5. Quick and Detailed Udhaar.** `ledger_entry_items`; quick udhaar dialog (W4) and itemized form (W5) with live preview and item-name suggestions; reverse/correct UI for credit sales. Exit: L30–L34 tests; under 10 seconds for find-and-record on the counter PC.

**6. Payment Collection.** Wasooli dialog with full-balance shortcut, overpayment and advance handling, advance refund and adjustment (owner), printable receipt (W6). Exit: L40–L46 tests; receipt prints correctly on A4 and a narrow preview.

**7. Customer Khata and Statements** (in the `Ledger` module, S4). Khata page with running balance and reversal labels; statement print view with presets, customer copy, and corrections notice (W8). Exit: golden-scenario statement matches [Ledger rules §7](DIGITAL_KHATA_LEDGER_RULES.md#7-worked-example-golden-scenario) exactly.

**8. Dashboard and Reports** (owner only). KPIs, top dues, aging by last wasooli, daily/period summary, corrections log, corrected vs as-recorded views (W9, L62, L64). Exit: totals equal hand-computed golden values.

**9. Backup and Data Safety.** `khata:backup` (mysqldump + photos + manifest + checksums), external copy, retention, schedule, backup list and download (owner + password confirmation), CSV exports, `khata:restore` through a scratch database, restore drill (W10).

**10. Production Readiness and Real-Shop Testing.** Production `.env`, dedicated MySQL user with `SELECT, INSERT` on ledger tables, Windows auto-start, PHP limits (`upload_max_filesize ≥ 3M`, `memory_limit ≥ 256M`), runbook, a pilot week with real register data, owner sign-off.

---

## Decision Record

Approved by the owner on 16 Oct 2026 unless marked open.

| #   | Decision                                                                                                  | Applied in                                       |
| --- | --------------------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| D1  | Staff manage customers and khata operations; no shop-wide reports or owner settings                       | Architecture §6.4                                |
| D2  | Owner and Staff roles are required                                                                        | 2B ✓                                             |
| D3  | Exact paisa storage; whole-rupee display, non-zero paisa always shown                                     | Architecture §6.1, L32                           |
| D4  | Reversals effective at the original's date, original kept, actual recording time kept, no silent rewrites | L53, L64, Ledger rules §3                        |
| D5  | Owner-only `adjustment` type                                                                              | L45                                              |
| D6  | Pakistani lakh-style grouping                                                                             | Architecture §6.1                                |
| D7  | Staff backdating ≤ 7 days, warning over 30 days, explicit and tested                                      | L22                                              |
| D8  | English labels with Udhaar, Wasooli, Baqaya                                                               | Vision §3                                        |
| D9  | Public registration disabled                                                                              | 2A ✓                                             |
| D10 | Photos ≤ 2 MB with in-browser downscaling                                                                 | Architecture §6.7                                |
| D11 | Email-format login names; no outbound email                                                               | 2A ✓                                             |
| D12 | Email forgot-password removed; offline reset command                                                      | 2A ✓                                             |
| D13 | No customer credit limits in V1                                                                           | L46                                              |
| D14 | Configurable inactivity timeout, not a hard-coded 12 hours: 30 idle minutes (`SESSION_LIFETIME`)          | 2B ✓                                             |
| S1  | No balance cache, subject to accuracy and performance tests                                               | Database §5, L60–L61                             |
| S2  | Receipt number = entry id; generated customer codes from id                                               | Database                                         |
| S3  | No triggers; immutability guarantees documented and tested on MySQL                                       | Database §4                                      |
| S4  | Statements and khata screens in the `Ledger` module; customer list links                                  | Architecture §3                                  |
| S5  | No "paid now" in itemized udhaar                                                                          | L35 withdrawn                                    |
| S6  | One warning-confirmation flag; corrections keep owner + reason + password confirmation                    | L25, L26                                         |
| —   | `activity_logs` moves to Phase 3; the `audit` log channel covers 2A/2B changes                            | Database                                         |
| —   | Documentation fixes I1–I5                                                                                 | Vision W1, W2, W9; Architecture §2; this roadmap |
| —   | Demo accounts are locked, never deleted                                                                   | 2A ✓                                             |
| —   | Remember Me removed, because it conflicts with an unattended counter (owner direction for 2B)             | 2B ✓                                             |
| —   | Strong passwords: at least 10 characters with letters and numbers, app-wide (owner direction for 2B)      | 2B ✓                                             |
| —   | Accounts are deactivated, never deleted; a shop always keeps one active owner                             | 2B ✓                                             |

### Open Decisions

| #   | Question                                                                                                                                          | Proposal                                                          | Needed by |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- | --------- |
| O2  | May staff print a single customer's statement (not shop-wide reports)?                                                                            | Yes; it is part of serving that customer                          | 7         |
| O3  | Root `/`: keep the starter marketing page or redirect to the dashboard/login?                                                                     | Redirect; the marketing page has no purpose in a private shop app | 2C        |
| O4  | Confirm the reading of D4: corrected view by default, a corrections notice on affected periods, and an "as recorded at" view (§3 of Ledger rules) | As written                                                        | 4         |
