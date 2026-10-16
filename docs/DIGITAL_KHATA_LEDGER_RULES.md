# Digital Khata — Ledger Rules

Status: approved direction for the Phase 4 posting engine and its tests (Phase 4 has not started). Each rule ID (`L…`) must be covered by at least one automated test that names it, for example `it('rejects a second reversal (L51)')`. Rule IDs are stable; withdrawn rules keep their number. Schema details: [Database](DIGITAL_KHATA_DATABASE.md).

## 1. Model

Each customer has one **receivable sub-ledger**: an ordered list of immutable entries. The balance is the sum of the entries.

- **Sign convention:** `amount_paisa > 0` increases what the customer owes; `amount_paisa < 0` decreases it.
- **Balance meaning:** `> 0` the customer owes the shop (**Baqaya**) · `< 0` the shop holds an **Advance** for the customer · `= 0` settled.
- **Active entry:** an entry that is not a reversal and has not been reversed.
- **Effective date** (`entry_date`) is when the business event happened. **Recorded at** (`created_at`) is when it was entered. Both are kept on every row and neither ever changes.

| Type              | UI label            | Purpose                                                      | Sign (original) | Who          | Extra fields                       |
| ----------------- | ------------------- | ------------------------------------------------------------ | --------------- | ------------ | ---------------------------------- |
| `opening_balance` | Opening balance     | Carry-over from the paper register                           | + or −          | Owner        | `description` = register reference |
| `credit_sale`     | Udhaar              | Goods on credit, quick (no items) or itemized                | +               | Owner, Staff | optional items                     |
| `payment`         | Wasooli (payment)   | Money received (partial, full, or advance)                   | −               | Owner, Staff | `payment_method`                   |
| `refund`          | Advance returned    | Advance paid back to the customer                            | +               | Owner        | `payment_method`                   |
| `adjustment`      | Adjustment / chhoot | Discount, write-off, returned goods, or other business event | + or −          | Owner        | `reason` required                  |

A **reversal** is a row with the **same type** as its original and the **opposite amount**, linked by `reversal_of_id`. Because the type is kept, every aggregate grouped by type (for example, collections) nets reversals automatically.

## 2. Rules

### General

- **L1** Every change to a customer's balance is a ledger entry. No other table, form, or command can change a balance.
- **L2** Amounts are whole paisa (`BIGINT`) and never zero. Floats are never used for money in PHP, SQL, or stored data.
- **L3** The sign follows the type table above. It is enforced by application validation and the `le_sign_by_type` CHECK.
- **L4** User input is a positive rupee string with at most 2 decimals. The direction comes from the type (and the opening-balance direction choice), never from a user-typed minus sign.
- **L5** Entries and items are immutable. The guarantees and their tests are listed in [Database §4](DIGITAL_KHATA_DATABASE.md#4-immutability-guarantees-s3-no-triggers): model guards, an architecture test against query-builder writes, CHECK/unique/FK constraints tested on MySQL, and `SELECT, INSERT`-only production grants.
- **L6** An entry belongs to exactly one customer of the same shop (`entry.shop_id = customer.shop_id`).
- **L7** Each entry records `created_by` and `created_at`. Its `id` is the receipt number.
- **L8** A single entry's absolute amount is at most `config('khata.money.max_entry_paisa')` (default Rs 10,000,000).
- **L9** A posting is atomic. Entries and items commit together or not at all (one transaction, customer rows locked first).

### Posting Preconditions

- **L20** The customer exists in the current shop and is not archived. This applies to every posting, including reversals.
- **L21** `entry_date ≤ today` in the shop timezone, and `entry_date ≥ 2000-01-01`. Future dates are rejected for every role.
- **L22** Backdating. Let `age = today − entry_date` in whole days, with `today` taken in the shop timezone at posting time.
    - **Staff:** `age ≤ staff_backdate_days` (default **7**) is allowed; `age > 7` is rejected with "Only the owner can record entries older than 7 days."
    - **Owner:** any age allowed by L21.
    - **Warning (any role):** `age > backdate_warning_days` (default **30**) requires confirmation (L25).
    - Example with today = 16 Oct: staff may date 09 Oct (age 7) but not 08 Oct (age 8). The owner may date 16 Sep (age 30) without a warning; 15 Sep (age 31) asks for confirmation.
    - The limits apply to every entry whose date a user chooses, including a correction's replacement. A reversal's date is copied from its original (L52), so the limits do not apply to it.
    - Both defaults live in `config/khata.php` and are covered by boundary tests (age 7/8, 30/31, and 0/−1).
- **L23** If the customer has an active opening balance, `entry_date` must be on or after its date.
- **L24** Idempotency: each form submission carries a client-generated UUID. A repeat with the same `(shop_id, idempotency_key)` posts nothing. It returns the original entry when customer, type, and amount match; otherwise it returns a validation error ("This form was already submitted — reopen it").
- **L25** Warnings use one confirmation flag (approved S6). Inside the locked transaction the server computes the warnings: possible duplicate (same customer, type, and amount within 2 minutes), overpayment, payment on a zero or negative balance, and backdating over 30 days. If any apply and the request lacks `confirmed: true`, the post is rejected with 422 and the warning texts, and the UI shows them and resubmits with `confirmed: true`. The server recomputes on resubmission, so warnings always reflect the balance at posting time.
- **L26** Sensitive corrections keep their stronger confirmation regardless of L25. Reversal, correction, refund, adjustment, and opening balance require the owner role. Reversal and correction also require a reason and a recent password confirmation (`auth.password.confirm`), and a dialog showing the balance before and after.

### Opening Balance

- **L10** `type = opening_balance`, either sign, `entry_date` = the as-of date from the register.
- **L11** At most one active opening balance per customer.
- **L12** Its date must be on or before every other active entry's date for that customer. Posting is rejected and lists the conflicting entries otherwise.
- **L13** Owner only. To change one, use a correction (reverse and replace), so the original remains visible.

### Itemized Udhaar

- **L30** Items exist only on original (non-reversal) `credit_sale` entries, 1–50 lines.
- **L31** Quantity > 0 with at most 3 decimals, at most 100,000. Unit is one of `piece`, `dozen`, `packet`, `kg`, `gram`, `litre`, `ml`. Unit price is ≥ 0 with at most 2 decimals, at most the L8 maximum.
- **L32** `line_total_paisa = intdiv(quantity_milli × unit_price_paisa + 500, 1000)`, which rounds half up to the nearest paisa per line. Bounds from L31 keep the product below 10^17, inside 64-bit range.
- **L33** `amount_paisa = Σ line_total_paisa`, and it must be > 0.
- **L34** The server ignores client totals. The frontend preview uses the same formula from `lib/money.ts` for display only.
- **L35** _Withdrawn (approved S5)._ There is no "paid now" field. Cash received at purchase time is recorded as a separate payment immediately after.

### Payments, Refunds, Adjustments

- **L40** A payment is stored as negative (`−amount`) and `payment_method` is required. Methods are recorded only; nothing is integrated.
- **L41** An overpayment (amount > positive balance) is allowed after confirmation. The resulting negative balance is shown as **Advance**.
- **L42** A payment on a zero or negative balance (an advance deposit) is allowed after confirmation.
- **L43** Later udhaar consumes an advance automatically through the sum. No allocation table exists, because payments are not matched to individual purchases in V1.
- **L44** A refund is owner-only and requires `balance_before < 0` and `amount ≤ |balance_before|`, so it can bring the balance to zero at most.
- **L45** An adjustment is owner-only, requires a reason, and may have either sign. It is for real business events (discount "chhoot", bad-debt write-off, returned goods). Data-entry mistakes use reversal or correction, not adjustment.
- **L46** There are no credit limits in V1 (approved decision).

### Reversal And Correction

- **L50** Reversal is owner-only, requires a reason, and requires a recent password confirmation (L26).
- **L51** An entry can be reversed at most once (`UNIQUE reversal_of_id`).
- **L52** A reversal copies the original's customer, type, `payment_method`, and `entry_date`, and has `amount = −original.amount`. It has no items of its own.
- **L53** **Dating.** A reversal's effective date is the original's `entry_date`, because the mistaken event never happened. Its `created_at` is the actual moment of correction. The original row is never modified. Section 3 states how reports show this so that history is never silently rewritten.
- **L54** A reversal cannot itself be reversed. To undo a mistaken reversal, post a new, correct entry (or correct the replacement).
- **L55** A correction is one transaction: a reversal of the original plus a replacement of the **same type** with `replaces_entry_id = original.id`. The replacement may change the customer, amount, date, description, items, or method. It must satisfy every normal rule for its own customer (L20–L23, L30–L45). A replacement is an ordinary entry and can itself be corrected later.
- **L56** Reversals and corrections are never blocked because of the resulting balance (truth wins). The confirmation dialog shows the balance before and after.
- **L57** Reversing an opening balance frees the L11 slot for a new opening balance.

### Balances And Reporting

- **L60** Customer balance = `SUM(amount_paisa)` over all of the customer's entries, reversals included. It is always computed from the ledger; no balance is stored (approved S1).
- **L61** _Changed by S1._ There is no balance cache to reconcile. Phase 4 must prove both accuracy (the seeded random posting/reversal test compares every displayed balance with an independent in-test sum) and performance ([Database §5](DIGITAL_KHATA_DATABASE.md#5-balances-without-a-cache-s1)).
- **L62** Period figures use effective `entry_date` and group by type, so reversals net automatically:
    - Udhaar given = `Σ amount (credit_sale)`
    - Collections (Wasooli) = `−Σ amount (payment)`
    - Advances returned = `Σ amount (refund)`
    - Net cash in = Collections − Advances returned
    - Adjustments = `Σ amount (adjustment)` (negative = discounts or write-offs)
    - Total receivable = `Σ positive balances`; total advances = `−Σ negative balances`
- **L63** The running balance is ordered by `(entry_date, id)` and computed at read time (`SUM(amount_paisa) OVER (PARTITION BY customer_id ORDER BY entry_date, id)`). Statement opening balance = `SUM(amount_paisa) WHERE entry_date < from`. No `balance_after` is stored, because backdated inserts would invalidate it.
- **L64** **As recorded at.** Every report and statement can be reproduced exactly as it was known at an instant `T` by adding `created_at ≤ T` to its query. Printed reports carry their generation time `T`, so a reprint "as printed" matches the original paper.

### Customers

- **L70** A customer can be archived only when their balance is exactly 0. Archived customers keep all history, cannot receive postings (L20), and still appear in historical reports.
- **L71** A customer with any ledger entry cannot be deleted (FK restrict). Only customers with no entries can be deleted.

## 3. How Reversals Appear In History (No Silent Rewrites)

A reversal changes figures for its original's effective date. To keep that visible:

1. **Originals stay.** The mistaken entry is never edited or hidden in the office view. Its reversal sits next to it, labelled "Reversed on 16 Oct 2026 14:05 by Owner — reason: wrong amount".
2. **Corrections notice on every period report and statement.** When any reversal or replacement dated inside the period was recorded after the period ended, the report shows a notice. It states how many there are and the net amount, with a link to the corrections log. Example: "Includes 2 corrections recorded after 31 Oct (net −Rs 300)."
3. **Two views of any period:**
    - _Corrected_ (default): the truth by effective date, all corrections included.
    - _As recorded at…_ (L64): the figures exactly as they stood at a chosen time, for example when the day's cash was counted or a statement was printed.
4. **Corrections log** (Phase 8): every reversal and replacement ordered by `created_at`, with who, why, both dates, and the amount.
5. **Customer copy** of a statement may collapse reversed pairs for readability, but the footer states that it does and how many pairs were collapsed. The office copy always shows them.

Example: a Rs 500 payment dated 30 Oct is reversed on 2 Nov. October collections in the corrected view drop by Rs 500 and show the notice. The "as recorded at 31 Oct 23:59" view still shows the original figure, matching the paper count from that night.

## 4. Posting Procedure (Engine Contract)

```text
post(request):
  BEGIN (retry deadlocks ×3)
    customers ← SELECT … WHERE id IN (…) AND shop_id=? FOR UPDATE ORDER BY id   -- L6, L20, L9
    validate role, dates, OB window, type rules, reversal state                  -- L10–L13, L21–L23, L26, L44, L51–L55
    warnings ← compute(balance_before = SUM(amount_paisa), request)              -- L25, L60
    if warnings ≠ ∅ and not request.confirmed → ROLLBACK, 422 {warnings}
    INSERT ledger_entries (+ items)                                              -- L2, L3, L30–L33
  COMMIT
  after commit: LedgerEntryPosted event (activity, cache invalidation)
on duplicate idempotency_key → resolve per L24 (no retry)
```

## 5. Concurrency Scenarios

| Scenario                                                           | Outcome                                                                                          |
| ------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------ |
| Double-click / browser retry of the same form                      | Same idempotency key: one entry, same receipt shown (L24)                                        |
| Two devices take payments for the same customer at once            | Serialized by the customer row lock; both post; the second sees the updated balance for warnings |
| Owner reverses an entry while staff posts udhaar for that customer | Serialized; both succeed; balance = sum of all                                                   |
| Two reversals of the same entry at once                            | The first wins; the second fails `UNIQUE reversal_of_id` → "already reversed" (L51)              |
| Correction moving an entry from customer A to B                    | Both rows locked in ascending id order; reversal on A and replacement on B commit together       |

## 6. Verification Command

`php artisan khata:ledger:verify` exits non-zero when it finds any issue. It checks:

1. Every reversal: same customer, type, method, and date; negated amount; original not a reversal (L52–L54).
2. Itemized totals equal the sum of lines; items only on original credit sales (L30, L33).
3. At most one active opening balance per customer, dated on or before their other active entries (L11, L12).
4. Sign, method, and reason rules hold for every row (L3, L40, L45), which catches anything inserted outside the engine.
5. No entry's effective date is after its `created_at` shop-local date (L21).

It runs in the test suite, after every restore (Phase 9), and is scheduled nightly with output logged.

## 7. Worked Example (Golden Scenario)

Used by both an automated feature test and [Manual QA §4](DIGITAL_KHATA_MANUAL_QA.md#4-golden-ledger-scenario-phases-47). Customers: Aslam (C-0001) and Bilal (C-0002).

| #   | Date   | Customer    | Action                                                              | Amount (Rs) | Aslam balance  |
| --- | ------ | ----------- | ------------------------------------------------------------------- | ----------- | -------------- |
| 1   | 01 Oct | Aslam       | Opening balance "Register 2 p.45", customer owes                    | +2,500      | 2,500          |
| 2   | 05 Oct | Aslam       | Quick udhaar "doodh, double roti"                                   | +450        | 2,950          |
| 3   | 06 Oct | Aslam       | Itemized: sugar 2 kg × 160; oil 1 litre × 540; eggs 1 dozen × 360   | +1,220      | 4,170          |
| 4   | 07 Oct | Aslam       | Wasooli, cash                                                       | −1,000      | 3,170          |
| 5   | 08 Oct | Aslam       | Quick udhaar (mistake, belongs to Bilal)                            | +300        | 3,470          |
| 6   | 08 Oct | Aslam→Bilal | Correct #5: reversal on Aslam (dated 08 Oct) + replacement on Bilal | −300 / +300 | 3,170          |
| 7   | 10 Oct | Aslam       | Wasooli, cash, overpayment confirmed                                | −3,500      | −330 (advance) |
| 8   | 11 Oct | Aslam       | Quick udhaar                                                        | +200        | −130           |
| 9   | 12 Oct | Aslam       | Advance returned, cash (owner)                                      | +130        | 0              |

Expected:

- Aslam's balance is Rs 0 and Bilal's is Rs 300. The verify command reports 0 issues.
- Statement for Aslam from 06 Oct to 10 Oct: opening Rs 2,950; rows #3, #4, #5, the #5 reversal (labelled with its recording time), and #7; closing Advance Rs 330.
- Collections 01–12 Oct = Rs 4,500; advances returned = Rs 130; net cash in = Rs 4,370; udhaar given (both customers, opening balances excluded) = Rs 2,170.
- If step 6 is recorded on 15 Oct, an 08 Oct report shows the corrections notice, and its "as recorded at 14 Oct" view still shows Rs 300 udhaar for Aslam (L64).
- Rejected attempts: refunding Rs 1 when Aslam's balance is 0 (L44); reversing #5 again (L51); reversing the #5 reversal (L54); posting udhaar for Aslam dated 30 Sep (L23); staff dating an entry 8 days back (L22); archiving Bilal at Rs 300 (L70).
- Rounding and display (L32, decided D3): 0.750 kg × Rs 220 = Rs 165 · 0.333 kg × Rs 100 = Rs 33.30 · 0.125 kg × Rs 37.50 = 4.6875 → **Rs 4.69** · 3 piece × Rs 0 = Rs 0 (allowed only if the entry total is > 0). Whole amounts display without decimals; non-zero paisa always display.
