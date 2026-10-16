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

| #   | Step                                                                                                                                                           | Expected                                                                                                        |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| B1  | Open `http://127.0.0.1:8000` while logged out                                                                                                                  | Since 2C: the login page (no landing page). No "Get started" or "Create account" anywhere                       |
| B2  | Open `http://127.0.0.1:8000/auth/register`                                                                                                                     | 404 page                                                                                                        |
| B3  | Open `/auth/forgot-password` and `/auth/reset-password/anything`                                                                                               | 404 page for both                                                                                               |
| B4  | Open `/auth/login`                                                                                                                                             | No "Forgot password?" link. The text "Forgot your password? Ask the shop owner to reset it." is shown           |
| B5  | Log in with `admin@app.com` / `Admin123!@#`                                                                                                                    | Rejected ("These credentials do not match our records")                                                         |
| B6  | Log in with the owner login and password from T2                                                                                                               | Dashboard opens directly; **no** "verify your email" screen                                                     |
| B7  | As owner, open Settings → Profile, Password, Appearance, and Users                                                                                             | All load as before                                                                                              |
| B8  | `/` while logged in (since 2C)                                                                                                                                 | Goes straight to the dashboard                                                                                  |
| B9  | Log out, then use the browser Back button                                                                                                                      | Protected pages are not shown from history; you are sent to login                                               |
| B10 | Stay logged in in one browser. In a terminal run `php artisan khata:user:reset-password <owner login>` with a new password. Then click any page in the browser | You are sent to the login page (the database session was revoked). The old password fails and the new one works |
| B11 | Wrong password 6 times quickly on the login page                                                                                                               | Throttled with a "Too many login attempts" message (existing behaviour, unchanged)                              |

## 2b. Phase 2B Checklist (Owner And Staff Management)

### Setup

```powershell
Set-Location D:\digital-khata
git switch feat/phase-2b-staff-management
php artisan migrate        # adds users.deactivated_at; no data is changed
composer dev               # open http://127.0.0.1:8000
```

In your own `.env`, change `SESSION_LIFETIME=120` to `SESSION_LIFETIME=30` (the new default; your file overrides it), then restart `composer dev`. Use two browsers or a normal and a private window: **A** for the owner, **B** for staff.

### Browser Checks

| #   | Step                                                                                                                                                                                       | Expected                                                                                                                                                                                                                          |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| S1  | A: log in as the owner                                                                                                                                                                     | The login form has no "Remember me". The sidebar shows **Staff** (not "Users")                                                                                                                                                    |
| S2  | A: Staff → **Add staff**: name `Ali Raza`, login `ali@shop.local`, role Staff, password and confirmation `Counter-Staff-2026`                                                              | Toast "Ali Raza can now log in as ali@shop.local". The row shows a **Staff** pill and an **Active** pill                                                                                                                          |
| S3  | A: try Add staff with password `abc12345`, then `only-letters-here`, then mismatched confirmation, then login `ALI@shop.local`                                                             | Field errors: at least 10 characters; at least one number; confirmation does not match; "The login has already been taken." No account is created                                                                                 |
| S4  | B: log in as `ali@shop.local` / `Counter-Staff-2026`                                                                                                                                       | The dashboard opens directly, with **no** "verify your email" screen. The sidebar has no Staff item                                                                                                                               |
| S5  | B: type `/app/admin/users` in the address bar                                                                                                                                              | 403 page                                                                                                                                                                                                                          |
| S6  | A: edit Ali, change only the name to `Ali R.`. B: click any page                                                                                                                           | B stays signed in                                                                                                                                                                                                                 |
| S7  | A: edit Ali, set new password `Fresh-Staff-Pass-7` (twice). B: click any page                                                                                                              | B is sent to the login page. The old password fails; the new one works                                                                                                                                                            |
| S8  | A: edit Ali, change login to `ali.raza@shop.local`. B: click any page                                                                                                                      | B is signed out; logging in with the new login works                                                                                                                                                                              |
| S9  | A: open Ali's row menu → **Deactivate**, read the dialog, confirm. B: click any page                                                                                                       | The row shows **Deactivated**. B lands on login with "Your account is deactivated. Ask the shop owner to reactivate it."                                                                                                          |
| S10 | B: log in as Ali with the correct password, then with a wrong one                                                                                                                          | Correct password: the deactivated message. Wrong password: "These credentials do not match our records."                                                                                                                          |
| S11 | A: Ali's menu → **Reactivate**, confirm. B: log in as Ali                                                                                                                                  | Toast "… can log in again"; B reaches the dashboard                                                                                                                                                                               |
| S12 | A: look at your own row and edit yourself                                                                                                                                                  | Your row says "(you)" and has no Deactivate action. In the edit dialog the role is locked with "You cannot change your own role."                                                                                                 |
| S13 | A: Settings → Profile                                                                                                                                                                      | There is no "Delete account" section                                                                                                                                                                                              |
| S14 | Log in as the owner in **B** too. A: Settings → Password, change your password. Then click in A and in B                                                                                   | A stays signed in; B is sent to login                                                                                                                                                                                             |
| S15 | A: search `staff`, then `owner`                                                                                                                                                            | Each search lists only accounts with that role                                                                                                                                                                                    |
| S16 | Narrow A to phone width (about 360 px) and open Staff                                                                                                                                      | Cards show name, login, role and status; the row menu works                                                                                                                                                                       |
| S17 | Log out. Log in with `admin@app.com` / `Admin123!@#`, then `user@app.com` / `User123!@#`                                                                                                   | Both rejected: demo accounts stay locked                                                                                                                                                                                          |
| S18 | Wrong password 6 times in a row for one login                                                                                                                                              | "Too many login attempts. Please try again in … seconds."                                                                                                                                                                         |
| S19 | Idle timeout: set `SESSION_LIFETIME=1` in `.env`, restart `composer dev`, log in, wait 2 minutes, then click a link. Repeat, but this time leave a form open and submit it after 2 minutes | Link: the login page. Form submit: the login page with "Your session has expired. Please log in again." (wording since 2C; since 2C the link click shows it too) and no error dialog. Afterwards, set `SESSION_LIFETIME=30` again |
| S20 | Optional: add a second owner, then try to make yourself Staff                                                                                                                              | Error on Role: "You cannot remove your own Owner role." The other owner can be deactivated; you cannot                                                                                                                            |

## 2c. Phase 2C Checklist (Shop Settings, Navigation, Authentication UX)

### Setup

```powershell
Set-Location D:\digital-khata
git switch feat/phase-2c-shop-settings
php artisan migrate        # expected: "Nothing to migrate." (2C adds no migration)
composer dev               # open http://127.0.0.1:8000
```

In your own `.env`, change `APP_NAME="laravel-starter-kit"` to `APP_NAME="Digital Khata"` (the new `.env.example` value), then restart `composer dev`. The session cookie name comes from `APP_NAME`, so everyone is signed out once after this change; that is expected. Keep `SESSION_LIFETIME=30`.

Use two browsers or a normal and a private window: **A** for the owner, **B** for the staff account from 2B (`ali.raza@shop.local`, or add a new staff account on the Staff screen first).

### Browser Checks

| #   | Step                                                                                                                                                                                                  | Expected                                                                                                                                                                                                                |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| C1  | A: log in as the owner and look at the sidebar                                                                                                                                                        | Top: notebook icon, **Digital Khata**, and your shop name under it. Group **Menu**: Dashboard. Group **Manage shop**: Staff, Shop settings. Footer: **My account**, **Password**                                        |
| C2  | A: open **Shop settings**                                                                                                                                                                             | "Shop details" form filled with the current name, phone, address, and receipt footer. "Business time" card: "Pakistan Standard Time (PKT, UTC+05:00)", "Asia/Karachi … cannot be changed", and today's date in Pakistan |
| C3  | A: set name `Bismillah Karyana Store`, phone `0300-1234567`, address `Shop 4, Main Bazaar, Lahore`, receipt footer `Shukriya! Phir tashreef laiye.`, then click **Save changes**                      | The button shows a spinner and is disabled while saving. Toast "Shop settings saved." The sidebar shop name changes immediately. "Details last saved" shows the current Pakistan time                                   |
| C4  | A: refresh the page                                                                                                                                                                                   | All four values are still there                                                                                                                                                                                         |
| C5  | A: set the name to `   Bismillah    Store   ` (extra spaces) and save                                                                                                                                 | Saved and shown as `Bismillah Store`                                                                                                                                                                                    |
| C6  | A: empty phone, address, and receipt footer, then save; refresh                                                                                                                                       | Saved; the three fields stay empty                                                                                                                                                                                      |
| C7  | A: clear the shop name and save                                                                                                                                                                       | Field error "Enter the shop name." Nothing is saved (refresh shows the old name)                                                                                                                                        |
| C8  | A: phone `0300-CALL-NOW`, then `12345`, then `0300--1234567`; save each time                                                                                                                          | Letters and doubled dashes: "Use digits with optional spaces or dashes, for example 0300-1234567 or +92 300 1234567." `12345`: "must be at least 7 characters". Nothing is saved                                        |
| C9  | A: paste a shop name longer than 120 characters and save                                                                                                                                              | "The shop name field must not be greater than 120 characters."                                                                                                                                                          |
| C10 | A: use only the keyboard: Tab through the fields, press Enter in the name field. Then double-click **Save changes**                                                                                   | Tab order is name → phone → address → receipt footer → Save. Enter saves. A double-click saves once (one toast)                                                                                                         |
| C11 | B: log in as staff and look at the sidebar and the user menu (bottom of the sidebar)                                                                                                                  | Only **Menu** → Dashboard, plus **My account** and **Password**. No Manage shop group, no Staff, no Shop settings                                                                                                       |
| C12 | B: type `http://127.0.0.1:8000/app/shop/settings` in the address bar. Then click **Back to Digital Khata**. Repeat with `/app/admin/users`                                                            | 403 page with a "Back to Digital Khata" link, which returns to the dashboard. Same for the Staff screen                                                                                                                 |
| C13 | Root URL: in A and B open `http://127.0.0.1:8000/`. In a new private window (logged out) open it too                                                                                                  | A and B: the dashboard. Logged out: the login page with the Digital Khata brand. No starter-kit landing page anywhere                                                                                                   |
| C14 | A: deactivate the staff account. B: open `http://127.0.0.1:8000/`. Then A reactivates it                                                                                                              | B lands on login with "Your account is deactivated. Ask the shop owner to reactivate it." (not the dashboard). No redirect loop                                                                                         |
| C15 | A: log out from the user menu, then press the browser Back button                                                                                                                                     | Login page with "You have logged out." Back does not show protected pages                                                                                                                                               |
| C16 | Session expiry: set `SESSION_LIFETIME=1` in `.env`, restart `composer dev`, log in, wait 2 minutes, click **Dashboard** in the sidebar. Repeat, leaving Shop settings open and saving after 2 minutes | Both times: the login page with "Your session has expired. Please log in again." and no error dialog. Afterwards set `SESSION_LIFETIME=30` again and restart                                                            |
| C17 | Revoked session: B logged in. A edits the staff account and sets a new password. B clicks any link                                                                                                    | B lands on login with "Your session has expired. Please log in again."                                                                                                                                                  |
| C18 | Pakistan time: open the Dashboard                                                                                                                                                                     | Heading "Assalam-o-Alaikum, <your name>" and "<shop name> · <weekday>, <day> <month> <year>" matching today's date in Pakistan                                                                                          |
| C19 | Pakistan time is independent of the computer: in Windows Settings → Time & language, turn off "Set time zone automatically" and pick (UTC-05:00) Eastern Time. Reload Dashboard and Shop settings     | The business date and "Details last saved" do not change: they stay in Pakistan time. Then set the Windows time zone back to (UTC+05:00) Islamabad, Karachi                                                             |
| C20 | Optional, between 12:00 AM and 5:00 AM Pakistan time: open the Dashboard                                                                                                                              | It shows the new Pakistan date, even though UTC is still on the previous day                                                                                                                                            |
| C21 | Mobile: narrow A to phone width (about 360 px). Check the header, open the menu, and open Shop settings, Dashboard, and the login page (in B)                                                         | The header shows the shop name beside the menu button. The menu opens as a panel with both groups. Shop settings shows the form, then the Business time card, with no sideways scrolling. Login is usable               |
| C22 | A: My account → Appearance → Dark, then open Shop settings and Dashboard                                                                                                                              | Text, cards, and field errors are readable in dark mode                                                                                                                                                                 |
| C23 | A: My account → change your name and save; Password → change password (old password, new one twice)                                                                                                   | Both save as before ("Saved."). You stay signed in in A                                                                                                                                                                 |
| C24 | Open `storage/logs/audit-<today>.log`                                                                                                                                                                 | `audit.shop_settings_updated` lines list only the changed fields with before and after values. No passwords anywhere                                                                                                    |

### Regression Smoke (2A And 2B)

| #   | Step                                                                                             | Expected                                                                                    |
| --- | ------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------- |
| R1  | Open `/auth/register`, `/auth/forgot-password`                                                   | 404 for both                                                                                |
| R2  | `php artisan khata:install`                                                                      | "Digital Khata is already installed. Nothing was changed."                                  |
| R3  | Log in with `admin@app.com` / `Admin123!@#`                                                      | Rejected                                                                                    |
| R4  | A: Staff → add a staff account, edit its name, deactivate it, reactivate it (2B S2, S6, S9, S11) | Each works with its toast; there is no Delete action anywhere, including Settings → Profile |
| R5  | Wrong password 6 times for one login                                                             | "Too many login attempts. Please try again in … seconds."                                   |

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
|      | 2B    |        |        |       |
|      | 2C    |        |        |       |
