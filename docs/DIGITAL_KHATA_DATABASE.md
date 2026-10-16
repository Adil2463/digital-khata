# Digital Khata — Database Design

Status: `shops` and `users.shop_id` are **implemented (Phase 2A)**. Everything else is an approved proposal for its phase. The target is MySQL 8.0.46 (the local server), InnoDB, `utf8mb4_unicode_ci`, strict mode. Migrations use Laravel's schema builder. CHECK constraints the builder cannot express are added with driver-specific `DB::statement` calls.

Rules referenced as `L…` are defined in [Ledger rules](DIGITAL_KHATA_LEDGER_RULES.md).

## 1. Conventions

- Primary keys are `BIGINT UNSIGNED AUTO_INCREMENT` (`$table->id()`).
- Money columns are signed `BIGINT` paisa with a `_paisa` suffix. Quantities are `BIGINT UNSIGNED` thousandths with a `_milli` suffix. No `FLOAT`, `DOUBLE`, or `DECIMAL` money columns.
- Every business table has `shop_id BIGINT UNSIGNED NOT NULL` referencing `shops(id)` with `ON DELETE RESTRICT`. Composite unique keys start with `shop_id`.
- Foreign keys default to `RESTRICT`. **Nothing financial cascades.**
- Timestamps are UTC. Business dates are `DATE` in the shop timezone.
- Immutable tables have `created_at` only (the model sets `UPDATED_AT = null`).
- No cached balances, counters, or sequence tables: balances are summed from the ledger (S1), and receipt numbers and generated customer codes come from row ids (S2).

## 2. Entity Overview

```text
shops 1──* users                       (users.shop_id; implemented)
shops 1──* customers 1──* ledger_entries 1──* ledger_entry_items
ledger_entries 0..1──1 ledger_entries   (reversal_of_id, unique)
ledger_entries 0..1──1 ledger_entries   (replaces_entry_id, unique)
ledger_entries *──1 users               (created_by)
shops 1──* activity_logs *──0..1 users  (actor_id)
shops 1──* backups                      (Phase 9)
```

## 3. Tables

### `shops` — Implemented (Phase 2A)

Migration `2026_10_16_000001_create_shops_table`.

| Column                     | Type                  | Notes                                                                 |
| -------------------------- | --------------------- | --------------------------------------------------------------------- |
| `id`                       | BIGINT UNSIGNED PK    |                                                                       |
| `name`                     | VARCHAR(120) NOT NULL | Printed on receipts                                                   |
| `phone`                    | VARCHAR(20) NULL      | Edited on the shop settings page (Phase 2C)                           |
| `address`                  | VARCHAR(255) NULL     | Edited on the shop settings page (Phase 2C)                           |
| `timezone`                 | VARCHAR(64) NOT NULL  | Default `Asia/Karachi`; `khata:install` validates it (`timezone:all`) |
| `receipt_footer`           | VARCHAR(255) NULL     | e.g. "Shukriya!" (Phase 2C)                                           |
| `created_at`, `updated_at` | TIMESTAMP NULL        |                                                                       |

The currency is fixed at PKR and is not stored. `khata:install` refuses to create a second shop.

### `users` — Additions

| Column           | Type                                                  | Phase | Notes                                                                                                                      |
| ---------------- | ----------------------------------------------------- | ----- | -------------------------------------------------------------------------------------------------------------------------- |
| `shop_id`        | BIGINT UNSIGNED NULL, FK `shops` `ON DELETE RESTRICT` | 2A ✓  | Migration `2026_10_16_000002_add_shop_id_to_users_table`. `khata:install` sets it for the owner and every existing account |
| `deactivated_at` | TIMESTAMP NULL                                        | 2B    | Deactivated users cannot log in; history stays attributable                                                                |

Users created through the Users screen before Phase 2B receive `shop_id = NULL`. Phase 2B assigns the creating owner's shop and makes the column required in application validation.

### `customers` (Phase 3)

| Column                     | Type                       | Notes                                                                                               |
| -------------------------- | -------------------------- | --------------------------------------------------------------------------------------------------- |
| `id`                       | BIGINT UNSIGNED PK         |                                                                                                     |
| `shop_id`                  | BIGINT UNSIGNED FK         |                                                                                                     |
| `code`                     | VARCHAR(20) NOT NULL       | Custom (old register number) or generated as `C-` + zero-padded id in the creating transaction (S2) |
| `name`                     | VARCHAR(120) NOT NULL      | Trimmed, internal whitespace collapsed                                                              |
| `phone`                    | VARCHAR(20) NULL           | As entered, for display                                                                             |
| `phone_normalized`         | VARCHAR(15) NULL           | Digits, country form `923001234567`; used for search and duplicate warnings                         |
| `address`                  | VARCHAR(255) NULL          |                                                                                                     |
| `notes`                    | VARCHAR(500) NULL          |                                                                                                     |
| `photo_path`               | VARCHAR(255) NULL          | Relative to the private `customer_photos` disk; server-generated `{shop}/{customer}/{ulid}.webp`    |
| `photo_updated_at`         | TIMESTAMP NULL             | Cache-busting version for the photo URL                                                             |
| `archived_at`              | TIMESTAMP NULL             | Only when the balance is 0 (`L70`)                                                                  |
| `created_by`               | BIGINT UNSIGNED FK `users` |                                                                                                     |
| `created_at`, `updated_at` | TIMESTAMP NULL             |                                                                                                     |

Indexes and constraints:

- `UNIQUE (shop_id, code)`. The collation is case-insensitive, so `c-1` and `C-1` collide. A custom code matching the generated pattern for a future id is rejected (`C-` + digits is reserved for generated codes).
- `INDEX (shop_id, archived_at, name)` for the default list sorted by name.
- `INDEX (shop_id, phone_normalized)` for phone lookups and duplicate warnings.
- No unique constraint on phone or name. Duplicates are warned about, never blocked ([Vision W1](DIGITAL_KHATA_VISION.md#w1-add-customer-phase-3)).
- Search uses `LIKE '%term%'` on `name`, `code`, `address`, and `phone_normalized`. At the expected size (≤20,000 customers) this takes milliseconds. A FULLTEXT ngram index on `(name, address)` is the upgrade path if ever needed.
- Customer merge is out of scope for V1. The workaround is to correct entries onto the surviving customer and archive the other.

### `ledger_entries` (Phase 4) — Immutable

| Column              | Type                                    | Notes                                                                                  |
| ------------------- | --------------------------------------- | -------------------------------------------------------------------------------------- |
| `id`                | BIGINT UNSIGNED PK                      | Also the receipt number (S2) and the posting order within a date                       |
| `shop_id`           | BIGINT UNSIGNED FK                      |                                                                                        |
| `customer_id`       | BIGINT UNSIGNED FK `customers` RESTRICT |                                                                                        |
| `type`              | VARCHAR(20) NOT NULL                    | `opening_balance`, `credit_sale`, `payment`, `refund`, `adjustment`                    |
| `amount_paisa`      | BIGINT NOT NULL                         | Signed: + increases what the customer owes, − decreases it                             |
| `entry_date`        | DATE NOT NULL                           | Effective business date in the shop timezone; may be backdated (`L22`)                 |
| `description`       | VARCHAR(255) NULL                       | Free text / register reference                                                         |
| `payment_method`    | VARCHAR(20) NULL                        | Required for `payment` and `refund`                                                    |
| `reason`            | VARCHAR(255) NULL                       | Required for reversals and adjustments                                                 |
| `reversal_of_id`    | BIGINT UNSIGNED NULL FK self RESTRICT   | Set only on reversal rows                                                              |
| `replaces_entry_id` | BIGINT UNSIGNED NULL FK self RESTRICT   | Set on the replacement posted by a correction                                          |
| `idempotency_key`   | CHAR(36) NOT NULL                       | Client-generated UUID per form open                                                    |
| `created_by`        | BIGINT UNSIGNED FK `users` RESTRICT     |                                                                                        |
| `created_at`        | TIMESTAMP NOT NULL                      | Actual recording instant (UTC). Never changes. Drives "as recorded at" reports (`L64`) |

Unique keys:

- `UNIQUE (shop_id, idempotency_key)` (`L24`)
- `UNIQUE (reversal_of_id)`: an entry can be reversed at most once (`L51`). MySQL allows multiple NULLs.
- `UNIQUE (replaces_entry_id)`: an entry can be replaced at most once.

Indexes:

- `(shop_id, customer_id, entry_date, id, amount_paisa)`: khata, statements, running balance, and covering `SUM` for one or all customers (S1, `L60`).
- `(shop_id, entry_date, type)`: daily totals, collections, reports.
- `(shop_id, created_at)`: "recorded today", corrections log, and as-recorded reports (`L64`).

CHECK constraints (MySQL 8.0.16+; FK columns used in CHECKs must not have cascading actions, which RESTRICT satisfies):

```sql
CONSTRAINT le_amount_nonzero  CHECK (amount_paisa <> 0),
CONSTRAINT le_type_valid      CHECK (type IN ('opening_balance','credit_sale','payment','refund','adjustment')),
CONSTRAINT le_sign_by_type    CHECK (
    type IN ('opening_balance','adjustment')
    OR (reversal_of_id IS NULL     AND ((type IN ('credit_sale','refund') AND amount_paisa > 0) OR (type = 'payment' AND amount_paisa < 0)))
    OR (reversal_of_id IS NOT NULL AND ((type IN ('credit_sale','refund') AND amount_paisa < 0) OR (type = 'payment' AND amount_paisa > 0)))
),
CONSTRAINT le_method_scope    CHECK ((type IN ('payment','refund')) = (payment_method IS NOT NULL)),
CONSTRAINT le_method_valid    CHECK (payment_method IS NULL OR payment_method IN ('cash','bank_transfer','easypaisa','jazzcash','other')),
CONSTRAINT le_reason_required CHECK ((reversal_of_id IS NULL AND type <> 'adjustment') OR reason IS NOT NULL),
CONSTRAINT le_reversal_not_replacement CHECK (reversal_of_id IS NULL OR replaces_entry_id IS NULL)
```

Cross-row rules (a reversal has the same customer and type as its original, the exact negated amount, and the original's date; the original is not itself a reversal) are enforced in the locked posting transaction and re-checked by `khata:ledger:verify` (`L52`–`L54`).

### `ledger_entry_items` (Phase 5) — Immutable

| Column             | Type                        | Notes                                             |
| ------------------ | --------------------------- | ------------------------------------------------- |
| `id`               | BIGINT UNSIGNED PK          |                                                   |
| `ledger_entry_id`  | BIGINT UNSIGNED FK RESTRICT | Parent must be a non-reversal `credit_sale`       |
| `line_number`      | SMALLINT UNSIGNED NOT NULL  | 1..50                                             |
| `description`      | VARCHAR(120) NOT NULL       | Free text; no inventory link                      |
| `quantity_milli`   | BIGINT UNSIGNED NOT NULL    | 0.250 kg = 250; CHECK `> 0`; ≤ 100,000,000        |
| `unit`             | VARCHAR(10) NOT NULL        | `piece`,`dozen`,`packet`,`kg`,`gram`,`litre`,`ml` |
| `unit_price_paisa` | BIGINT UNSIGNED NOT NULL    | ≤ 1,000,000,000                                   |
| `line_total_paisa` | BIGINT UNSIGNED NOT NULL    | Server-computed (`L32`)                           |

Constraints: `UNIQUE (ledger_entry_id, line_number)`, `CHECK (quantity_milli > 0)`, `CHECK (unit IN (…))`. `SUM(line_total_paisa) = parent.amount_paisa` (`L33`) is enforced by the engine and re-checked by the verify command. Reversal rows carry no items; statements show the original's items next to the reversal.

### `activity_logs` (Phase 3) — Append-Only

Moved from Phase 2 (approved). Until then, the existing `audit` log channel covers what changes: user management (`audit.user_management`), installation (`audit.shop_installed`), and console password resets (`audit.password_reset`).

| Column         | Type                            | Notes                                              |
| -------------- | ------------------------------- | -------------------------------------------------- |
| `id`           | BIGINT UNSIGNED PK              |                                                    |
| `shop_id`      | BIGINT UNSIGNED FK              |                                                    |
| `actor_id`     | BIGINT UNSIGNED NULL FK `users` | NULL for console/system actions                    |
| `action`       | VARCHAR(64) NOT NULL            | e.g. `customer.updated`, `customer.photo_replaced` |
| `subject_type` | VARCHAR(64) NOT NULL            | Morph alias (`customer`, `shop`, `user`)           |
| `subject_id`   | BIGINT UNSIGNED NOT NULL        |                                                    |
| `changes`      | JSON NULL                       | `{field: {before, after}}`; secrets redacted       |
| `ip_address`   | VARCHAR(45) NULL                |                                                    |
| `user_agent`   | VARCHAR(255) NULL               | Truncated                                          |
| `created_at`   | TIMESTAMP NOT NULL              |                                                    |

Indexes: `(shop_id, subject_type, subject_id, id)` and `(shop_id, created_at)`.

### `backups` (Phase 9)

`id, shop_id, file_name VARCHAR(191) UNIQUE, size_bytes BIGINT UNSIGNED, sha256 CHAR(64), schema_version VARCHAR(191)` (last migration name), `customers_count, entries_count BIGINT UNSIGNED, net_balance_paisa BIGINT, external_copy_path VARCHAR(255) NULL, status VARCHAR(16)` (`completed`/`failed`), `error VARCHAR(500) NULL, created_by NULL FK users, created_at, verified_at NULL`.

## 4. Immutability Guarantees (S3: No Triggers)

Database triggers were dropped (approved S3). Immutability of `ledger_entries` and `ledger_entry_items` rests on these layers, each tested:

| Layer                                                         | Protects against                                                      | Verified by                                                                                                                       |
| ------------------------------------------------------------- | --------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Model guards (`updating`/`deleting` throw)                    | Application code editing or deleting an entry through Eloquent        | Pest tests (SQLite and MySQL groups)                                                                                              |
| Architecture test                                             | Query-builder or raw `UPDATE`/`DELETE` on ledger tables in `app/`     | Pest arch test forbidding `DB::table('ledger_…')` writes and mass `update()`/`delete()` on ledger queries                         |
| CHECK constraints, unique keys, FK `RESTRICT`                 | Invalid rows, double reversal, deleting referenced customers or users | `composer test:mysql` (Pest `group('mysql')`) against a real MySQL test database                                                  |
| Production DB grants (`SELECT, INSERT` only on ledger tables) | Anything outside the application, including tinker and manual SQL     | MySQL-group test creates a temporary restricted user, asserts `UPDATE`/`DELETE` are denied, then drops it; Phase 10 runbook check |
| `khata:ledger:verify`                                         | Rows that violate cross-row rules, however they got there             | Feature tests plus nightly schedule                                                                                               |

A database administrator with full privileges can still change rows; this is inherent to a local single-machine install and is mitigated by daily backups with checksums (Phase 9).

| Data                                   | Mutable?    | How it changes                  |
| -------------------------------------- | ----------- | ------------------------------- |
| `ledger_entries`, `ledger_entry_items` | Never       | Reversal / correction rows only |
| `activity_logs`                        | Never       | Append only                     |
| `customers` profile fields, photo      | Yes         | Logged to `activity_logs`       |
| `customers.archived_at`                | Yes (owner) | Only at zero balance            |
| `shops`, `users`                       | Yes (owner) | Audit log / `activity_logs`     |

## 5. Balances Without A Cache (S1)

A customer's balance is `SUM(amount_paisa)` over their entries. The customer list joins one grouped aggregate over the covering index above. There is no `customer_balances` table, so there is nothing that can drift and nothing to rebuild.

Approval condition: Phase 4 adds a performance test that seeds 200,000 entries across 20,000 customers in MySQL and requires the sorted, paginated customer list query and a single khata balance to finish within an agreed budget (proposed: 300 ms list, 20 ms single balance on the shop PC). If the budget is missed, a cache is reintroduced only with the owner's approval.

## 6. Posting Atomicity

Every money-changing request is one `DB::transaction(…, attempts: 3)` that first locks the customer row(s) (`SELECT … FOR UPDATE`, ascending id when two customers are involved) and then inserts every entry and item. Deadlock and lock-timeout errors are retried up to 3 times. A unique-key violation on `idempotency_key` is not retried; it resolves to the existing entry (`L24`). Side effects run after commit.

## 7. Archival And Retention

- Ledger rows are never deleted or archived. Expected volume is about 50–200 entries per day, under 75,000 rows per year.
- Customers are archived (`archived_at`), never deleted once they have entries. Hard delete is allowed only for customers with no entries.
- `activity_logs` are kept indefinitely.
- Backup zips are pruned by count (default: keep 30) after a successful new backup.
- Year-end closing is not needed. Statements compute an opening balance for any start date.

## 8. Migration Plan By Phase

| Phase | Migrations                                                                             |
| ----- | -------------------------------------------------------------------------------------- |
| 2A ✓  | `2026_10_16_000001_create_shops_table`, `2026_10_16_000002_add_shop_id_to_users_table` |
| 2B    | `add_deactivated_at_to_users_table`                                                    |
| 3     | `create_customers_table`, `create_activity_logs_table`                                 |
| 4     | `create_ledger_entries_table` (+ CHECKs)                                               |
| 5     | `create_ledger_entry_items_table` (+ CHECKs)                                           |
| 9     | `create_backups_table`                                                                 |

Every migration has a working `down()`. Dropping ledger tables in `down()` is acceptable only because production rollback is replaced by restore-from-backup, as stated in the Phase 10 runbook.
