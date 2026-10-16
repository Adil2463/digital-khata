# Digital Khata — Architecture

Status: Phases 2A, 2B, and 2C are implemented; later sections are the approved direction for their phases. `AGENTS.md` remains the canonical guide to the starter-kit conventions. This document records only the Digital Khata decisions layered on top of it. If this document and the code disagree, the code wins and this document must be updated.

## 1. Findings From The Repository

| Area                | What exists (verified in code)                                                                                                                                                                                                     | Consequence for Digital Khata                                                                                                                                                        |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Module discovery    | `ModuleRegistry` discovers `Routes/{web,api,gates,channels}.php`, `Policies/*Policy.php`, `Listeners/`, and `Providers/*.php` under `app/Modules/*`. Modules missing from the priority lists load after the prioritized ones.      | New modules need no manual registration.                                                                                                                                             |
| Policy mapping      | `ModuleRegistry::modelClassFromPolicyClass()` maps `FooPolicy` to `App\Models\Foo` **only**.                                                                                                                                       | Domain models with module policies (`Shop`, `Customer`, `LedgerEntry`) live in `app/Models`.                                                                                         |
| Console commands    | `bootstrap/app.php` auto-loads commands only from `app/Modules/Shared/Console/Commands`.                                                                                                                                           | Module commands are registered by the module's auto-discovered `Providers/ModuleServiceProvider.php` (implemented for `Shop` and `Auth` in 2A).                                      |
| Write orchestration | `Users`: Request → `toDto()` → `UserCommandHandler` → `UserCommands` → `UserManagementEvent` (`ShouldDispatchAfterCommit`) → listeners.                                                                                            | Ledger postings follow the same shape. A rolled-back posting emits nothing.                                                                                                          |
| Audit               | The daily `audit` log channel (30 days) records user management, `khata:install`, and console password resets.                                                                                                                     | Enough until Phase 3. Customer changes go to the `activity_logs` table from Phase 3; the ledger audits itself.                                                                       |
| Transport           | `DataFormRequest` / `DataQueryRequest` hydrate Spatie Data DTOs. `PageResponder` renders Inertia pages. `PaginationData` provides the shared page envelope.                                                                        | Reused as-is. Every khata payload is a `#[TypeScript]` DTO consumed from `@/types/app-data`.                                                                                         |
| Auth                | Session login, email verification (`verified`), password confirmation (`auth.password.confirm`), Sanctum, `manage-users` and `manage-shop` gates. Roles `admin`/`user`. **Registration and email reset were removed in 2A.**       | Password confirmation is reused for reversals and backups. Since 2B, owner-created accounts are verified, deactivated accounts lose access per request, and there is no Remember Me. |
| Time                | `config/app.php` timezone is `UTC`. `config/data.php` serializes DTO dates as `Y-m-d`.                                                                                                                                             | UTC timestamps; business dates in the shop timezone through `ShopClock` (2C). Instants in DTOs use an explicit ATOM transformer.                                                     |
| Storage             | `local` disk = `storage/app/private` with `'serve' => true` (signed `storage/{path}` routes). `public` disk is web-accessible.                                                                                                     | Customer photos get their own private disk with no `serve` and no URL.                                                                                                               |
| Security headers    | CSP `img-src 'self' data: blob:`, `nosniff`, frame, and referrer headers from `SecurityHeaders`.                                                                                                                                   | Same-origin photo URLs and `blob:` previews work without CSP changes.                                                                                                                |
| Database            | MySQL 8.0.46 (CHECK constraints, window functions), `utf8mb4_unicode_ci`, strict mode. **Tests run on in-memory SQLite.**                                                                                                          | MySQL-only enforcement is tested by a dedicated MySQL group from Phase 4.                                                                                                            |
| Runtime             | PHP 8.4 (64-bit ints, GD with WebP, fileinfo, bcmath, intl), `mysqldump` 8.0, `upload_max_filesize=2M`. Quality gates must run from `D:\digital-khata`; a lowercase `d:` working directory breaks Vitest and Larastan path checks. | No new packages needed. Raise `upload_max_filesize` to at least 3M before Phase 3.                                                                                                   |
| Frontend            | `components/ui` → `components/base` (`Base*`) → `modules/*`. Schema forms, server tables, `appTheme` recipes, auto-imports, toasts.                                                                                                | Customer list, khata, and forms use these primitives. Printing uses Tailwind `print:` variants and theme recipes.                                                                    |

## 2. Starter-Kit Feature Disposition

| Feature                                    | Decision                                                                                           | Phase |
| ------------------------------------------ | -------------------------------------------------------------------------------------------------- | ----- |
| Login, logout, password change, appearance | Keep                                                                                               | —     |
| Password confirmation                      | Keep; gates reversals, corrections, restore, and backup download                                   | 4–9   |
| Public registration                        | **Removed** (routes, page, form, DTO, nav links, tests)                                            | 2A ✓  |
| Email forgot/reset password                | **Removed**; offline `khata:user:reset-password` command; login page says to ask the owner         | 2A ✓  |
| Seeded demo users                          | **Off by default**, never in production; `khata:install` locks any still using published passwords | 2A ✓  |
| Shop + verified owner                      | **`khata:install`** creates them once                                                              | 2A ✓  |
| Staff created/edited by owner              | **Verified without email; join the owner's shop**                                                  | 2B ✓  |
| Session inactivity timeout                 | **30 idle minutes (`SESSION_LIFETIME`); Remember Me removed**                                      | 2B ✓  |
| Users admin (`manage-users`)               | **Staff screen: Owner/Staff labels; deactivate/reactivate; no deletion**                           | 2B ✓  |
| Self account deletion (Settings → Profile) | **Removed**                                                                                        | 2B ✓  |
| Shop settings page, shop name in app shell | **Owner edits name, phone, address, receipt footer; timezone read-only `Asia/Karachi`**            | 2C ✓  |
| Marketing landing `/`                      | **Removed; `/` redirects guests to login and signed-in accounts to the dashboard (O3)**            | 2C ✓  |
| Dashboard placeholder                      | Replace with khata KPIs                                                                            | 8     |
| API v1 + Sanctum                           | Keep unchanged and unused; no khata API in V1                                                      | —     |
| Reverb realtime + queue                    | Keep infrastructure; not required by khata features in V1. Runtime choice in Phase 10              | 10    |
| Module generator, CI, quality gates        | Keep and extend                                                                                    | —     |

## 3. Module Boundaries

The **Ledger module owns every write that changes money** and every screen that shows a khata (approved S4). This avoids frontend modules importing each other: the customer list links to the khata page and its actions instead of opening ledger dialogs itself.

| Backend module                 | Owns                                                                                                                                      | Frontend module     | Phase |
| ------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------- | ------------------- | ----- |
| `Shop`                         | Shop record, `khata:install` (2A ✓); settings page, `manage-shop` gate (2C ✓)                                                             | `modules/shop`      | 2A/2C |
| `Auth` (existing)              | Login, verification, password confirmation, `khata:user:reset-password` (2A ✓)                                                            | `modules/auth`      | 2A    |
| `Users`, `Settings` (existing) | Staff accounts and personal settings, adjusted in 2B                                                                                      | existing            | 2B    |
| `Customers`                    | Customer CRUD, search, duplicate warnings, archive, photos, `activity_logs`                                                               | `modules/customers` | 3     |
| `Ledger`                       | Posting engine, all money-changing transports, `LedgerEntryPolicy`, `khata:ledger:verify`, khata page, receipts, statements (print views) | `modules/ledger`    | 4–7   |
| `Reports`                      | Outstanding, collections, aging, daily summary, corrections log                                                                           | `modules/reports`   | 8     |
| `Dashboard` (existing)         | KPI page backed by its own queries                                                                                                        | `modules/dashboard` | 8     |
| `Backup`                       | Backup/restore commands, backup list and download, CSV export                                                                             | `modules/backup`    | 9     |

Payments are not a separate module: a payment is a ledger entry type, and splitting it out would duplicate locking and validation or force the engine into `Shared`. `Reports` only reads.

## 4. Placement Of Shared Code

| Concern                                                                        | Location                                                     | Status / reason                                                  |
| ------------------------------------------------------------------------------ | ------------------------------------------------------------ | ---------------------------------------------------------------- |
| `Shop`                                                                         | `app/Models/Shop.php`                                        | 2A ✓                                                             |
| `CredentialRotator`, `DemoAccounts`                                            | `app/Modules/Shared/Auth`                                    | 2A ✓; used by `Shop` and `Auth` commands and the users migration |
| `Customer`, `LedgerEntry`, `LedgerEntryItem`, `ActivityLog` models             | `app/Models`                                                 | Used across modules; required by policy discovery                |
| `LedgerEntryType`, `PaymentMethod`, `ItemUnit` enums                           | `app/Enums`                                                  | Shared domain primitives, auto-exported to TypeScript            |
| `Money` value object (int paisa, parse/format), `RupeeAmount` rule             | `app/Modules/Shared/Money`                                   | Phase 4                                                          |
| `CurrentShop` resolver, `ShopClock` (today in shop timezone), `SharedShopData` | `app/Modules/Shared/Shop`, `app/Modules/Shared/Data`         | 2C ✓; used by shop settings, the dashboard, and the app shell    |
| `ShopTimezone` enum (supported business timezones: `Asia/Karachi`)             | `app/Enums`                                                  | 2C ✓; cast on `Shop::$timezone`, validated by `khata:install`    |
| `formatShopDate`, `formatShopDateTime` (display in the shop timezone)          | `resources/js/lib/datetime.ts` (auto-imported)               | 2C ✓; the single frontend date formatter                         |
| Domain config (limits, backdating, photo size, backup paths)                   | `config/khata.php`                                           | Created with its first setting                                   |
| `formatMoney`, `parseRupees`, line-total preview                               | `resources/js/lib/money.ts` (auto-imported)                  | Single frontend formatter; display only                          |
| Customer avatar (photo or initials)                                            | `resources/js/components/base/avatar/BaseCustomerAvatar.vue` | Used by customers, ledger, and reports pages                     |
| Print layout                                                                   | `resources/js/layouts/PrintLayout.vue` + `appTheme.print`    | Receipts, statements, reports                                    |

No generic repository, service bus, or accounting framework is introduced. Each concern uses the existing Query / Command / Handler / Responder vocabulary.

## 5. Write Path (Ledger Posting)

```text
FormRequest (authorize via policy, validate, RupeeAmount → int paisa)
  → toDto(): e.g. RecordPaymentData
  → LedgerCommandHandler::recordPayment(customer, data, LedgerActionContext)
      → LedgerCommands::post(PostingData)                 ← the only code that inserts ledger rows
          DB::transaction(attempts: 3):
            1. lock customer row(s), ascending id   (SELECT … FOR UPDATE)
            2. check rules (role, archive, dates, opening-balance window, reversal state)
            3. compute warnings from SUM(amount_paisa); reject unless confirmed (L25)
            4. insert ledger_entries (+ ledger_entry_items)
      → Event::dispatch(LedgerEntryPosted)  (ShouldDispatchAfterCommit)
  → redirect with flash, or receipt page
```

- **Duplicate submissions:** the client creates a UUID idempotency key when the form opens (in an event handler, never during render, so SSR stays deterministic). `UNIQUE (shop_id, idempotency_key)` is the backstop (`L24`).
- **Immutability:** see [Database §4](DIGITAL_KHATA_DATABASE.md#4-immutability-guarantees-s3-no-triggers).

## 6. Cross-Cutting Decisions

### 6.1 Money (decided)

- Stored as signed `BIGINT` **paisa** with exact precision. No floats in the backend or the database.
- Input is a rupee string (`^\d{1,8}(\.\d{1,2})?$`), parsed by `Money::fromRupees()` without float conversion.
- DTO fields carry a `_paisa` suffix (`amount_paisa: number`). JavaScript numbers are exact up to 2^53.
- **Display:** whole rupees without decimals (`Rs 1,250`); non-zero paisa are always shown (`Rs 1,250.50`).
- **Grouping:** Pakistani lakh style (`Rs 12,34,567`), implemented once in `lib/money.ts`.

### 6.2 Shop Scoping And The Multi-Shop Path

- Every business table carries `shop_id` from its first migration; `users.shop_id` exists since 2A. Composite unique keys and leading index columns start with `shop_id`.
- `CurrentShop` (2C ✓) resolves the shop from the authenticated user and never from request input; routes for the current shop carry no shop id. Queries scope explicitly (`whereBelongsTo($shop)`); there are no hidden global scopes.
- Policies check `$model->shop_id === $user->shop_id`. Route model binding for shop-owned models returns 404 across shops.
- Tests create a second shop to prove isolation, even though V1 runs one shop.
- Later multi-shop: replace `users.shop_id` with a `shop_user` pivot (role per shop) and add a shop switcher.

### 6.3 Time

- `created_at` holds UTC instants. `entry_date` is a `DATE` in the shop timezone (no DST in Pakistan).
- "Today" is always `ShopClock::today()` (2C ✓). An entry recorded at 00:30 PKT belongs to the new PKT day; the Pakistan day starts at 19:00 UTC. `ShopClock` names the shop timezone explicitly, so the server's and PHP's default timezones never matter.
- V1 supports only `Asia/Karachi` (`ShopTimezone`). The timezone is read-only on Shop Settings, and `khata:install` rejects any other value.
- The frontend formats instants with `lib/datetime.ts` in the shop timezone and never in the browser's timezone. Pages that show "now" receive it from the server so the server render and hydration agree.

### 6.4 Accounts, Roles, And Sessions

- **Login identifier:** an email-format login name. No email is ever sent, so `owner@shop.local` is fine (decided).
- **Recovery:** `php artisan khata:user:reset-password <email>` on the shop computer (2A ✓). The login page tells users to ask the owner.
- **Roles** keep the existing enum values (`admin`, `user`) to avoid breaking contracts. The UI labels them Owner and Staff. All checks are server-side policies or gates (2B ✓).
- **Who can sign in (2B ✓):** accounts that belong to a shop and are not deactivated. `EnsureAccountCanSignIn` re-checks every authenticated web and API request, so deactivation takes effect on the next click regardless of session driver.
- **Credentials (2B ✓):** a changed login or password, or a deactivation, revokes remember tokens, API tokens, and database sessions through `CredentialRotator`; editing your own account keeps the current session.
- **Sessions (2B ✓):** 30 idle minutes (`SESSION_LIFETIME`, configurable); no Remember Me, so an unattended counter always times out. An expired form submit returns to login with a message. Since 2C the next visit to login after any idle or revoked session says "Your session has expired", and logout says "You have logged out." (`SessionEndNotice`).
- **Brute force (2B ✓):** 5 attempts per login and IP, plus 20 per minute per IP across logins.

| Capability                                           | Owner |  Staff   |
| ---------------------------------------------------- | :---: | :------: |
| Search/view customers and khata, print receipts      |   ✓   |    ✓     |
| Create/edit customer, manage photo                   |   ✓   |    ✓     |
| Archive / restore / delete customer                  |   ✓   |          |
| Opening balance                                      |   ✓   |          |
| Udhaar (quick/itemized), wasooli                     |   ✓   |    ✓     |
| Backdate entries (L22)                               |  any  | ≤ 7 days |
| Refund, adjustment                                   |   ✓   |          |
| Reverse / correct (with password confirmation)       |   ✓   |          |
| Customer statement for a single khata                |   ✓   |    ✓     |
| Dashboard totals, shop-wide reports, corrections log |   ✓   |          |
| Shop settings, staff accounts, backup/restore        |   ✓   |          |

### 6.5 Audit

- **Money:** the ledger is the audit trail (`created_by`, `created_at`, `reason`, `reversal_of_id`, `replaces_entry_id`).
- **Until Phase 3:** the `audit` log channel covers user management, installation, and console password resets. Passwords are never logged.
- **From Phase 3:** `activity_logs` rows for customer edits, photo changes, archive, settings, and staff accounts, written after commit with the existing `MutationContext` + `MutationMetadata` pattern.

### 6.6 Printing

Receipts and statements are server-rendered Inertia pages with a print layout and `print:` utilities. The browser's "Save as PDF" covers PDF needs.

### 6.7 Customer Photos

| Concern        | Decision                                                                                                                                                                                                                                                            |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Disk           | New `customer_photos` disk: `local` driver, root `storage/app/private/customer-photos`, `throw => true`, **no `serve`, no `url`**. Outside `public/`; nginx executes only `public/index.php`, so stored files can never run.                                        |
| Validation     | `file`, `mimetypes:image/jpeg,image/png,image/webp` (content-sniffed by fileinfo), `max:config('khata.customer_photos.max_kb')` (default 2048), `dimensions:min_width=64,min_height=64,max_width=6000,max_height=6000`. GD must decode the file.                    |
| Processing     | The browser downscales to ≤1024 px before upload (also applying EXIF orientation). The server resizes to ≤800 px and re-encodes to WebP, which strips EXIF/GPS and destroys polyglot payloads. Stored files are always `.webp`.                                     |
| Naming         | `{shop_id}/{customer_id}/{ULID}.webp`, generated server-side. The client filename is ignored and no request field contains a path, so path traversal is impossible.                                                                                                 |
| Columns        | `customers.photo_path` (nullable) and `customers.photo_updated_at` (cache-busting `?v=`). No image bytes in the database.                                                                                                                                           |
| Serving        | `GET /app/customers/{customer}/photo` (`app.customers.photo.show`): `auth` + `verified` + `CustomerPolicy::view` (404 across shops). Streams with `image/webp`, `nosniff`, `inline`, `Cache-Control: private, max-age=86400`. Missing file → 404 → initials avatar. |
| Write ordering | Validate and encode → write the **new** file → transaction updates `photo_path` (+ activity log) → on DB failure delete the new file and rethrow → after commit delete the **old** file (failures logged for the pruner).                                           |
| Removal        | The transaction sets `photo_path = null`; the file is deleted after commit.                                                                                                                                                                                         |
| Orphans        | `khata:customer-photos:prune` (daily, `--dry-run`) deletes unreferenced files older than 1 hour.                                                                                                                                                                    |
| Isolation      | Photo handling runs after the customer save. A storage failure keeps the customer and flashes a warning. Ledger code never touches photos.                                                                                                                          |
| Backups        | The photo directory is part of the Phase 9 backup and restore.                                                                                                                                                                                                      |

### 6.8 Backup And Restore

`mysqldump --single-transaction --routines --no-tablespaces` against the `digital_khata` database only, plus `ZipArchive`. No new dependency. The restore flow is in [Vision W10](DIGITAL_KHATA_VISION.md#w10-export-and-restore-records-phase-9).

## 7. Testing Strategy

| Layer                            | Tooling                                                             | Examples                                                                                                                                                                                                                                                          |
| -------------------------------- | ------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Accounts and install (2A ✓)      | Pest feature                                                        | `khata:install` happy path, refusal, validation, demo locking and declining; reset command; removed routes return 404; seeding guards                                                                                                                             |
| Shop settings and time (2C ✓)    | Pest feature + unit; Vitest; MySQL run                              | Owner view/update, validation, staff 403, browser `shop_id` ignored, cross-shop isolation, missing shop context, `ShopClock` midnight/year boundaries independent of server timezone, frontend PKT formatting, root redirect, session notices, navigation by role |
| Accounts and sessions (2B ✓)     | Pest feature + unit; MySQL run                                      | Staff create/edit/deactivate/reactivate, shop scoping (404), last-owner and self rules, credential revocation, per-request deactivation on persisted sessions, 30-minute idle expiry, login throttles, no Remember Me, locked demo accounts                       |
| Pure rules                       | Pest unit                                                           | `Money` parsing/rounding, line totals, phone normalization, `ShopClock` day boundary, L22 boundaries                                                                                                                                                              |
| Posting engine                   | Pest feature (SQLite)                                               | Every rule L1–L71, idempotency, overpayment, double reversal, opening-balance window, L64 as-recorded queries                                                                                                                                                     |
| Accuracy (S1 condition)          | Seeded random sequence of postings, reversals, and corrections      | After each step every displayed balance equals an independent in-test sum; verify command reports zero issues                                                                                                                                                     |
| MySQL enforcement (S3 condition) | `composer test:mysql` (Pest `group('mysql')`, Phase 4)              | CHECKs, uniques, FK restrict, model guards, restricted-grant user cannot `UPDATE`/`DELETE`                                                                                                                                                                        |
| Performance (S1 condition)       | MySQL group with 200k seeded entries                                | Customer list and single balance within budget ([Database §5](DIGITAL_KHATA_DATABASE.md#5-balances-without-a-cache-s1))                                                                                                                                           |
| Transport and authorization      | Pest feature                                                        | Staff vs owner, cross-shop 404, validation, password confirmation on reversal                                                                                                                                                                                     |
| Photos                           | `Storage::fake('customer_photos')`, `UploadedFile::fake()->image()` | Upload, replace, remove, disguised PDF, oversize, huge dimensions, unauthorized/cross-shop 404, DB failure cleanup, pruner                                                                                                                                        |
| Frontend                         | Vitest                                                              | `lib/money.ts` (lakh grouping, paisa display), navigation, avatar fallback, idempotency key once per dialog                                                                                                                                                       |
| Manual                           | [Manual QA](DIGITAL_KHATA_MANUAL_QA.md)                             | Browser checklists, golden scenario, printing, restore drill                                                                                                                                                                                                      |

## 8. Decision Log

| #   | Decision                                                                                             | Alternatives rejected                                                                |
| --- | ---------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| A1  | Integer paisa (`BIGINT`), exact; whole-rupee display unless paisa non-zero                           | `DECIMAL(12,2)`; whole rupees only (itemized prices produce paisa)                   |
| A2  | Single-entry customer sub-ledger with a signed amount                                                | Full double-entry general ledger (premature)                                         |
| A3  | Reversal keeps the original `type` with the opposite sign                                            | A separate `reversal` type (every report would need to join the original)            |
| A4  | Balances always summed from the ledger (S1)                                                          | Cached projection table (drift and rebuild machinery)                                |
| A5  | Running balance computed at read time (window function)                                              | Stored `balance_after` (wrong after backdated inserts)                               |
| A6  | Ledger module owns all money writes and khata screens (S4)                                           | Separate `Payments`/`Statements` modules (duplicated engine or cross-module imports) |
| A7  | `shop_id` everywhere now, explicit scoping                                                           | Defer tenancy; global scopes                                                         |
| A8  | Models in `app/Models`                                                                               | Module-local models (policy discovery would not map them)                            |
| A9  | Photos re-encoded to WebP on a private disk, streamed by an authorized controller                    | Public disk; DB blobs; signed `local` serve URLs                                     |
| A10 | Browser print for PDFs                                                                               | A PDF dependency                                                                     |
| A11 | Receipt number = entry id; generated customer code from id (S2)                                      | Per-shop sequence table with its own lock                                            |
| A12 | No DB triggers; layered guarantees tested on MySQL (S3)                                              | Triggers (privilege and SQLite divergence)                                           |
| A13 | One warning-confirmation flag; stronger owner + password confirmation for corrections (S6)           | Named confirmation list                                                              |
| A14 | No public registration or email reset; console install and reset (2A)                                | Email-dependent flows on an offline machine                                          |
| A15 | Reversals effective at the original's date with corrections notices and as-recorded views (L53, L64) | Dating reversals today (leaves wrong historical balances); silent restatement        |
