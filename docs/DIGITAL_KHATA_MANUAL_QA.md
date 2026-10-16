# Digital Khata — Manual QA

Manual checks the owner runs at the end of each phase, alongside the automated gates. Record each run in §6 as pass or fail with the date. Write down the exact steps for anything that fails. Open the project and terminals at `D:\digital-khata` (uppercase drive letter).

## 1. Phase 1 Checklist (Foundation And Architecture) — Completed

Phase 1 delivered documents only and was approved. Its checks (docs-only diff, three original migrations, unchanged app, 197 PHP and 123 Vitest tests) are kept in Git history.

## 2. Phase 2A Checklist (Shop Foundation And Secure Installation)

### 2.1 Setup On Your Local Machine

Your local database currently contains the demo accounts `admin@app.com` and `user@app.com`, which use publicly known passwords. Installation will offer to lock them. Locking does not delete them, but you can no longer log in with the demo passwords. Pick the owner login you want to keep using.

```powershell
Set-Location D:\digital-khata
php artisan migrate                 # adds the shops table and users.shop_id
php artisan khata:install           # answer the prompts; the password is hidden while typing
composer dev                        # then open http://127.0.0.1:8000
```

Optional: set `APP_SEED_USERS=false` in your own `.env` (the default is now `false`; your file still says `true`). It matters only if you ever run `migrate:fresh`.

### 2.2 Terminal Checks

| #   | Step                                                                                     | Expected                                                                                                                                                                   |
| --- | ---------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T1  | `php artisan migrate:status`                                                             | `2026_10_16_000001_create_shops_table` and `2026_10_16_000002_add_shop_id_to_users_table` show **Ran**                                                                     |
| T2  | `php artisan khata:install`, entering a shop name, owner name, owner login, and password | Lists the two demo accounts and asks to lock them. Answer **yes**. Ends with "Digital Khata is installed", showing 2 locked demo accounts and 2 existing accounts attached |
| T3  | Read the whole T2 output and `storage/logs/audit-*.log`                                  | Your password appears nowhere                                                                                                                                              |
| T4  | Run `php artisan khata:install` again                                                    | "Digital Khata is already installed. Nothing was changed."                                                                                                                 |
| T5  | `php artisan khata:install --no-interaction` on a copy without a shop (optional)         | Refuses: the password is only accepted from a hidden prompt                                                                                                                |
| T6  | `php artisan khata:user:reset-password user@app.com`, entering mismatched passwords      | "confirmation does not match", "Nothing was changed."                                                                                                                      |
| T7  | `php artisan khata:user:reset-password nobody@shop.local`                                | "No account uses that login email."                                                                                                                                        |

### 2.3 Browser Checks

| #   | Step                                                                                                                                                           | Expected                                                                                                                                           |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| B1  | Open `http://127.0.0.1:8000` while logged out                                                                                                                  | The home page shows one **Log in** button. No "Get started" or "Create account" anywhere, including the mobile menu (narrow the window) and footer |
| B2  | Open `http://127.0.0.1:8000/auth/register`                                                                                                                     | 404 page                                                                                                                                           |
| B3  | Open `/auth/forgot-password` and `/auth/reset-password/anything`                                                                                               | 404 page for both                                                                                                                                  |
| B4  | Open `/auth/login`                                                                                                                                             | No "Forgot password?" link. The text "Forgot your password? Ask the shop owner to reset it." is shown                                              |
| B5  | Log in with `admin@app.com` / `Admin123!@#`                                                                                                                    | Rejected ("These credentials do not match our records")                                                                                            |
| B6  | Log in with the owner login and password from T2                                                                                                               | Dashboard opens directly; **no** "verify your email" screen                                                                                        |
| B7  | As owner, open Settings → Profile, Password, Appearance, and Users                                                                                             | All load as before                                                                                                                                 |
| B8  | Home page while logged in                                                                                                                                      | Shows **Open dashboard** and **Settings**                                                                                                          |
| B9  | Log out, then use the browser Back button                                                                                                                      | Protected pages are not shown from history; you are sent to login                                                                                  |
| B10 | Stay logged in in one browser. In a terminal run `php artisan khata:user:reset-password <owner login>` with a new password. Then click any page in the browser | You are sent to the login page (the database session was revoked). The old password fails and the new one works                                    |
| B11 | Wrong password 6 times quickly on the login page                                                                                                               | Throttled with a "Too many login attempts" message (existing behaviour, unchanged)                                                                 |

Known and expected in 2A (fixed in 2B): a staff account you create in Users will be asked to verify its email. Account self-deletion is still present under Settings → Profile; do not use it on the owner account.

## 3. Standing Checks (Every Later Phase)

- Log in as owner and as staff. Staff cannot reach owner-only screens, by menu **or** by typing the URL (expect 403 or 404).
- Every form: submit empty (clear messages); double-click submit (only one record created); press Enter to submit; Tab order is logical; usable at 360 px wide and at 200% zoom; dark mode readable.
- After any money action: the customer balance equals the last running balance in the khata. `php artisan khata:ledger:verify` reports 0 issues (Phase 4+).
- Refresh mid-flow and use browser Back: no duplicate entries, no stale balances.
- Amounts: whole rupees show no decimals, paisa show when non-zero, lakh grouping (`Rs 1,50,000`).

## 4. Golden Ledger Scenario (Phases 4–7)

Use a fresh database (`php artisan migrate:fresh` on a **copy**, never on real data). Run [Ledger rules §7](DIGITAL_KHATA_LEDGER_RULES.md#7-worked-example-golden-scenario) step by step through the UI. After each step, write down Aslam's displayed balance and compare it with the table. Then:

- [ ] Final balances: Aslam Rs 0, Bilal Rs 300.
- [ ] Each rejected attempt in the scenario shows a clear message and creates nothing.
- [ ] Aslam's statement 06–10 Oct: opening Rs 2,950, closing **Advance Rs 330**. The reversed entry and its reversal (with its recording time) are visible on the office copy and collapsed on the customer copy, whose footer says so.
- [ ] Item rounding: 0.125 kg × Rs 37.50 shows Rs 4.69.
- [ ] Receipt for entry 7 prints on one page with the shop name, customer, amount, method, balance after, and date.
- [ ] Reports (Phase 8): collections Rs 4,500, advances returned Rs 130, net cash in Rs 4,370, udhaar given Rs 2,170. A period containing a later-recorded correction shows the corrections notice; its "as recorded at" view shows the pre-correction figure.

## 5. Phase-Specific Scripts (Expanded When Each Phase Starts)

### Phase 2B — Staff Accounts And Session Security

- [ ] The owner creates a staff account; staff logs in without any email verification screen.
- [ ] Staff cannot open Users/Staff management.
- [ ] Deactivating staff logs them out on their next click; reactivating restores access; the owner cannot deactivate themself.
- [ ] Settings → Profile no longer offers account deletion.
- [ ] Leaving the browser idle longer than the configured timeout requires logging in again.

### Phase 2C — Shop Settings And App Shell

- [ ] The owner changes the shop name; the header shows it immediately. Staff get 403 on shop settings.

### Phase 3 — Customer Management (Including Photos)

- [ ] Create a customer with name only (code `C-…` generated); then with an Urdu name, a phone in `0300-1234567` form, an address, and notes.
- [ ] Search by part of the name, by `0300123`, by code, and by part of the address. Each finds the customer.
- [ ] Create a second customer with the same phone: a warning lists the first; "Create anyway" works.
- [ ] Custom code collision (`c-0001` vs `C-0001`) is rejected.
- [ ] Photo: upload a JPG, a PNG, and a WebP (each shows); upload a phone-camera photo larger than 2 MB (auto-downscaled, accepted, upright orientation).
- [ ] Photo: rename a PDF to `.jpg` (rejected); 5 MB image with downscaling disabled (rejected with the size message); 10000 × 10000 image (rejected).
- [ ] Replace the photo (new one shows, old file gone from `storage/app/private/customer-photos/...`); remove it (initials avatar shows).
- [ ] Copy the photo URL, log out, and open it (redirected to login). Opening `/storage/...` or `/customer-photos/...` paths directly returns 404.
- [ ] Stop MySQL briefly during a photo replace (or simulate it in a test). The old photo still shows and no orphan file remains after `php artisan khata:customer-photos:prune`.
- [ ] Archive a customer at zero balance (hidden from search, visible with "Include archived"); restore them.

### Phases 4–9

Detailed scripts are written at the start of each phase from Vision workflows W3–W10. The Phase 9 restore drill is mandatory: back up, run `migrate:fresh` on a scratch database, restore, verify, and compare manifest totals and three random customers' statements.

## 6. Run Log

| Date | Phase | Tester | Result | Notes |
| ---- | ----- | ------ | ------ | ----- |
|      | 2A    |        |        |       |
