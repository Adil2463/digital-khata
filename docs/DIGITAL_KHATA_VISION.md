# Digital Khata — Vision And Functional Specification

Status: approved product direction. Phase 2A (shop installation, no public registration, offline password reset) is implemented; every other workflow is planned for the phase named in its heading. Decisions are recorded in the [Roadmap](DIGITAL_KHATA_ROADMAP.md#decision-record).
Related: [Architecture](DIGITAL_KHATA_ARCHITECTURE.md) · [Database](DIGITAL_KHATA_DATABASE.md) · [Ledger rules](DIGITAL_KHATA_LEDGER_RULES.md) · [Roadmap](DIGITAL_KHATA_ROADMAP.md) · [Manual QA](DIGITAL_KHATA_MANUAL_QA.md)

## 1. Problem

A karyana shop in Pakistan keeps customer credit (udhaar) in paper registers. Finding a customer's page, adding up their balance, and recording purchases and payments is slow and error-prone. Disputes cannot be settled from the register's history, and the register can be lost or damaged.

## 2. Product Goal

A local, single-shop web application that makes three things fast and trustworthy:

1. **Find** any customer in seconds by name, phone, address, or customer code.
2. **Record** udhaar and payments in a few keystrokes, with no possibility of silent edits.
3. **Prove** every balance through an immutable, printable transaction history.

## 3. Users

| Role                          | Who                     | Needs                                                                                                              |
| ----------------------------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Owner (existing `admin` role) | Shopkeeper              | Everything: entries, corrections, reports, backups, settings, staff accounts                                       |
| Staff (existing `user` role)  | Family member or helper | Find and add customers, record udhaar and wasooli, view a customer's khata; no shop-wide reports or owner settings |
| Customer (not a system user)  | Shop customer           | Receives printed receipts and statements; never logs in                                                            |

Accounts use an email-format login name, but no email is ever sent; the owner creates staff accounts and resets forgotten passwords. Labels are English with familiar Urdu terms (Udhaar, Wasooli, Baqaya). Most users are not technical. The UI must work with large touch targets, high contrast, plain words, and the keyboard alone at the counter.

## 4. Success Measures

- Find a returning customer and record a quick udhaar entry in under 10 seconds.
- Every displayed balance equals the sum of that customer's ledger entries. The reconciliation command reports zero drift.
- No financial entry is ever updated or deleted; every correction stays visible.
- A backup can be restored onto a clean machine, and the restored balances match the backup manifest.

## 5. V1 Scope

In scope: owner login and shop settings; customers with optional photos; opening balances from paper registers; quick and itemized udhaar; full and partial payments; overpayment and advances; refunds of advances; owner-only adjustments; reversals and corrections; customer khata with a running balance; printable receipts and statements; dashboard and reports; local backup and tested restore.

Out of scope for V1: inventory, POS, barcode, SMS/WhatsApp APIs, AI, offline sync, online payments, multi-shop UI, a public website, and a mobile app API.

## 6. Product Principles

- **The ledger is the truth.** Balances are derived from entries. No one can type a balance.
- **Append-only.** Mistakes are fixed with visible reversal entries, never by editing or deleting.
- **Fast path first.** Quick udhaar is the most common action and gets the shortest flow.
- **Safe by default.** Confirm when something is unusual (overpayment, backdating, a possible duplicate); block when something is invalid.
- **Local and free.** Runs on one shop computer with MySQL. No paid services.

## 7. Glossary

| Term              | Meaning in the app                                                                         |
| ----------------- | ------------------------------------------------------------------------------------------ |
| Khata             | A customer's account: the list of ledger entries and the running balance                   |
| Udhaar            | Goods given on credit. Stored as a `credit_sale` entry; increases the balance              |
| Wasooli / Payment | Money received from a customer. Stored as a `payment` entry; decreases the balance         |
| Baqaya / Balance  | Positive: the customer owes the shop. Negative: the customer has an advance. Zero: settled |
| Advance           | A negative balance, created by an overpayment or a negative opening balance                |
| Opening balance   | The balance carried over from the paper register, as of a stated date                      |
| Reversal          | An entry that cancels a mistaken entry exactly. The original stays visible                 |
| Correction        | A reversal plus the correct replacement entry, recorded together                           |
| Paisa             | 1/100 rupee. All amounts are stored as whole paisa                                         |

---

## 8. Functional Specification

Each workflow lists its inputs, rules, and edge cases. Financial rules are numbered in [Ledger rules](DIGITAL_KHATA_LEDGER_RULES.md) (`L1`, `L2`, …) and referenced here.

### W1. Add Customer (Phase 3)

Inputs: name (required, 1–120 characters, Urdu or Roman Urdu allowed); phone (optional); address (optional, ≤255); notes (optional, ≤500); customer code (optional; generated from the customer's id as `C-0001` when blank); photo (optional, see W11). From Phase 4 the owner (not staff) also sees an optional opening balance on this form; when given, W3 runs in the same transaction.

Rules and edge cases:

- Phone is normalized to digits (`0300-1234567`, `+92 300 1234567`, and `923001234567` all become `923001234567`). Phone numbers that cannot be parsed are rejected with a clear message.
- **Duplicates warn but never block.** If an active customer has the same normalized phone or the same name (case-insensitive), the form lists those customers. The user must choose "Open existing" or "Create anyway". Families often share one phone, so duplicates are allowed.
- Customer codes are unique per shop. A duplicate custom code is a validation error. Generated codes come from a locked per-shop sequence and never repeat, even after archival.
- Leading and trailing whitespace is trimmed. Repeated internal spaces in names collapse to one space.
- Double submission is blocked in the UI while processing. The server rejects a second create carrying the same form token (idempotency key) and opens the existing customer instead.

### W2. Search Old Customer (Phase 3)

- One search box matches name, code, phone digits, and address, ignoring case and with partial matches. Archived customers are excluded unless "Include archived" is checked.
- A query made only of digits is also normalized as a phone fragment, so `0300123` finds `923001234567`.
- Each result shows the photo or initials avatar, name, code, phone, address excerpt, and current balance with its sign label.
- Sorting options: name, code, balance (highest dues first), and last activity. Results are paginated with the shared table stack.
- From the keyboard: `/` focuses search, the arrow keys move through results, and `Enter` opens the khata. From Phases 5–6, `U` and `P` open the khata page with the udhaar or wasooli dialog already open; they are plain links, so the customer list never embeds ledger dialogs.
- Empty state: "No customer found", with a button that creates a customer pre-filled from the query.

### W3. Record Opening Balance From The Old Register (Phase 4 engine, Phase 3/4 UI)

Inputs: customer; amount in rupees; direction ("Customer owes" or "Advance / shop owes customer"); as-of date; register reference (optional, e.g. "Register 2, page 45").

Rules: `L10`–`L13`. At most one active opening balance per customer (owner only). Its date must be on or before every other active entry for that customer. A zero opening balance is not recorded. Mistakes are fixed by correction (W7), which keeps the original visible.

Edge cases: customer already has entries dated before the as-of date (rejected with a list of the conflicting entries); an amount with more than 2 decimals (rejected); an as-of date in the future (rejected).

### W4. Quick Udhaar (Phase 5)

Inputs: customer; amount; description (optional, ≤255); date (defaults to today in the shop's timezone).

Rules: `L1`–`L9`, `L20`–`L24`. The amount must be greater than 0 and at most the configured maximum (Rs 10,000,000 by default). The posting is atomic and creates exactly one entry.

Edge cases:

- An archived customer cannot receive entries; restore the customer first.
- A possible accidental repeat (same customer, type, and amount within 2 minutes) asks "Record again?" before posting.
- Backdating follows `L22`: staff may date an entry up to 7 days back (older is rejected), the owner has no limit, and any entry older than 30 days asks for confirmation. Future dates are rejected.
- A customer with an advance: udhaar simply reduces the advance. No special handling is needed.

### W5. Detailed (Itemized) Udhaar (Phase 5)

Inputs: customer; date; 1–50 lines, each with item description (≤120), quantity (up to 3 decimals, > 0), unit (`piece`, `dozen`, `packet`, `kg`, `gram`, `litre`, `ml`), and unit price (rupees, up to 2 decimals); optional note.

Rules: `L30`–`L34`. The server computes every line total and the entry total. Client-side totals are only a preview and are ignored on submit. There is no "paid now" field: cash received at purchase time is recorded as a separate wasooli right after, which the khata page offers as the next action.

Edge cases: a fractional quantity times a price that produces sub-paisa amounts (rounded half-up per line, `L32`); an empty or blank line (removed before validation); every line removed (validation error); a zero unit price (allowed for a free item, but the entry total must be > 0). Amounts display as whole rupees unless they have paisa (Rs 165, Rs 4.69).

### W6. Receive Partial Or Full Payment (Phase 6)

Inputs: customer; amount; method (`cash`, `bank_transfer`, `easypaisa`, `jazzcash`, `other`; recording only, with no integration); date; note (optional).

Rules: `L40`–`L43`. A "Full balance" button fills the current outstanding amount. On success, the receipt (numbered by the entry id) can be printed right away.

Edge cases: the customer has zero or negative balance (allowed after confirmation, creating or increasing an advance); the amount exceeds the balance (confirm, then show "Advance: Rs X"); a duplicate submit (idempotency key returns the original receipt and posts nothing new); a refund of an advance (owner only, at most the current advance, `L44`).

### W7. Reverse Or Correct A Mistaken Entry (Phase 4 engine, Phases 5–7 UI)

- **Reverse:** the owner selects an entry, enters a mandatory reason, re-confirms their password (the existing `password.confirm` flow), and sees the balance before and after. A reversal entry with the opposite amount is posted (`L50`–`L56`). The original and the reversal stay visible and are labelled as linked, with the reversal's recording time. Reports dated in the corrected period show a corrections notice (Ledger rules §3).
- **Correct:** reverse plus post a replacement (corrected amount, date, description, items, or a different customer) in one transaction. The replacement links back to the original.
- Not allowed: reversing a reversal, reversing an entry twice, or editing or deleting any entry. To undo a mistaken reversal, post a new correct entry.
- **Wrong customer:** use Correct with a different customer. Balances for both customers change atomically.

### W8. Customer Statement (Phase 7)

Inputs: customer; date range (presets: this month, last month, all time, custom).

Output (printable page; save as PDF through the browser): shop header; customer name, code, phone, and photo; opening balance as of the start date; each entry in date order with type, description, items, debit, credit, and running balance; closing balance; and a generated-at timestamp with the user's name. A "customer copy" option collapses reversed pairs so customers see a clean history, and says so in its footer. The office copy always shows them. Owner and staff can print a single customer's statement; shop-wide reports are owner-only.

Edge cases: an empty range (shows opening = closing); a range that starts before the customer's first entry; reversals dated inside the range whose originals are outside it (not possible, because a reversal has its original's date, `L53`).

### W9. Calculate Outstanding Balances (Phases 4 and 8)

- **Customer balance** = sum of `amount_paisa` over all of the customer's entries, reversals included (`L60`).
- **Shop totals:** _total receivable_ = sum of positive balances; _total advances_ = sum of negative balances; _net_ = receivable − advances. Archived customers are always included in totals.
- **Collections (wasooli) for a period** = −(sum of `payment` entries) by `entry_date`. **Net cash in** = collections − advances returned (`refund` entries). Reversals net automatically (`L62`).
- Balances are always computed from the ledger; nothing is cached (`L60`, `L61`). `khata:ledger:verify` checks the rows themselves.

### W10. Export And Restore Records (Phase 9)

- **Backup:** the owner clicks "Create backup", or a daily scheduled job runs. It produces a zip with a MySQL dump of the `digital_khata` database only, the customer photos, and a manifest (counts, net balance, file checksums, app version). The zip is stored locally and copied to a configured external folder (USB drive).
- **Human-readable export:** CSV files of customers with balances and all ledger entries, readable in Excel and printable as a paper fallback.
- **Restore** is a console command run by the owner or a technician. It checks the checksums, switches the app to maintenance mode, restores into a scratch database first, runs ledger verification, compares totals with the manifest, and only then replaces the live data.
- Edge cases: a corrupt or partial zip (rejected before touching live data); a backup from a newer schema version (rejected); not enough disk space (checked first); an external drive that is not present (local backup still succeeds, with a warning).

### W11. Customer Profile Photo (Phase 3)

- Optional on create and edit. The photo can be replaced or removed at any time. If no photo is set, an initials avatar is shown in search results, the customer list, the customer detail page, and statements.
- Accepts JPG, PNG, and WebP up to a configurable maximum (default 2 MB, `KHATA_PHOTO_MAX_KB`). File content is checked on the server; the extension and filename are never trusted.
- To keep phone-camera photos under the limit, the browser downscales the image before upload. The server re-encodes every photo to WebP (stripping location metadata) and stores it on a private disk under a generated name.
- Photos are served only through an authorized route to logged-in users of the same shop, never from a public URL.
- A photo failure never blocks the customer save. Validation errors are shown on the photo field. If storage fails after validation, the customer is still saved and a warning toast says the photo was not saved.
- Full rules: [Architecture §6.7](DIGITAL_KHATA_ARCHITECTURE.md#67-customer-photos) and [Database `customers`](DIGITAL_KHATA_DATABASE.md#customers-phase-3).

### W12. Archive Customer (Phase 3)

Owner only. Allowed only when the balance is exactly zero; settle or adjust first. Archived customers are hidden from search by default, cannot receive new entries, and keep all their history. A customer can be hard-deleted only if they have no ledger entries (for example, created by mistake).
