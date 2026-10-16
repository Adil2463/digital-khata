# Starter kit branch audit

Audit date: 2026-09-09. Branch: `fix/starter-kit-architecture-and-type-safety-audit`.
Compared `origin/main` (`7a6dda1abe37bea1479bde1db28633bf234bfbbd`) with
`1f987b8ba26cad9bda46c82cea3f0bf6bca083da`, then reviewed the maintainer fixes in this checkout.
The branch contains four commits and changes 186 files before these fixes.
The initial working tree was clean. No matching pull request or referenced ticket was found.

## Audit Summary

The branch strengthens backend ownership, generated contracts, authentication,
transaction side effects, form accessibility, and reproducible checks. The review
found ten remaining defects or verification gaps and repaired them in the shared
implementations and generator. No additional framework, dependency upgrade, DTO
shape, database migration, or compatibility adapter was needed.

The review covered individual commit intent and cumulative behavior, including
requests/DTOs, web and API authorization, model updates, after-commit events and
listeners, shared page props, generated TypeScript/Wayfinder contracts, query and
mutation state, forms, tables, UI forwarding, SSR, realtime subscriptions,
scaffolding, dependency locks, CI, PM2 configuration, tests, and documentation.

## Commit-by-commit assessment

| Commit    | Intent and cumulative assessment                                                                                                                                                                                                                                                                                                                                                                                                                              |
| --------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `be8eedf` | Hardens contracts, security, typing, tooling, and runtime configuration. Current-cookie same-origin CSRF handling, model-owned verification reset, transaction-aware events, canonical pagination, and SSR cache isolation survive in the final branch. Query isolation still stopped at the cache boundary; findings 1 and 2 complete mounted-state and mutation handling. Identifier validation still allowed import collisions; finding 6 closes that gap. |
| `e4d20c3` | Consolidates primitive prop forwarding, pagination, PHP import sorting, and generated-module verification. The forwarding adapter preserves strict optional props without another runtime filter. Canonical pagination and sorted generated imports remain intact. Sorting did not validate conflicting PHP names. The generated-module check needed public-page and new-file coverage (findings 5–7).                                                        |
| `f546447` | Isolates scaffold verification, snapshots mutation event models, handles cancelled mutations, synchronizes server table state, and improves field labels. These changes remain present. Field labels were still ambiguous across multiple renderers (finding 3), and the shared renderer did not guard submission while processing (finding 4).                                                                                                               |
| `1f987b8` | Moves shared page props and realtime channel names into backend-owned contracts, wires stateful Sanctum authentication, hardens disabled/readonly controls, distinguishes cache key shapes, and stops stale retries. These improvements remain intact. Retry protection did not cover mounted data or delayed mutation callbacks. The commit removed the previous audit document; this file records the current review and verification instead.              |

There are no fixup, squash, or revert commits in this four-commit range. The later
commits refine the earlier architecture; the review found no unfinished terminology
migration or partially reverted implementation requiring a separate repair.

## Findings

### 1. Mounted queries retained data after account or key changes — high

Clearing the module cache did not clear a composable's existing `data` or `error`.
A mounted consumer could therefore continue displaying the previous account's
result after logout/account switching. Changing a query key also displayed the
previous record while its replacement loaded. The cache revision work in
`be8eedf`, extended for retries in `1f987b8`, did not notify mounted consumers.

**Fix:** `resources/js/composables/useApiQuery.ts` makes the existing cache epoch
reactive, synchronously resets mounted state and execution ownership on full
clears, and clears the previous result on a serialized key change. SSR cache
mutation helpers return without writing. Existing per-key invalidation and raw
response caching remain the canonical mechanisms.

**Verification:** regression tests cover a mounted result, an outstanding old
request completing after clear, and a key change before its new result arrives.
The failures were reproduced before the repair. Self-review also caught an
intermediate-key request regression in the initial fix: state clearing remains
synchronous, while automatic fetches retain Vue's batching. A regression test
verifies consecutive key changes do not fetch the intermediate key.

### 2. Delayed mutations crossed authenticated contexts — high

An old-account mutation could invalidate new-account cache entries, restore an
optimistic rollback, or invoke success/settlement callbacks after the root cleared
the cache. Asynchronous preparation could even finish and start a write with the
new account's cookies. The mutation lifecycle added/hardened in `be8eedf` and
`f546447` did not share the query identity boundary from `1f987b8`.

**Fix:** `useApiQuery.ts` captures the epoch for each mutation and checks it before
dispatch, after completion, and between asynchronous callbacks. Stale work rejects
through the existing typed error mapper with `stale_auth_context`; it cannot run
subsequent callbacks or invalidations. Mounted mutation data/errors reset on a full
clear. Pending counts still settle in `finally`.

**Verification:** tests cover late success, late failure, delayed preparation, and
account changes during both success and failure callbacks. All five cases failed
before their corresponding repairs. Existing optimistic rollback, concurrent
mutation, reset, callback-error, and custom-error tests remain in the suite.

### 3. Repeated forms generated duplicate control IDs — medium

The renderer used the field name as a document-wide ID. Two forms containing an
`email` or `name` field shared labels, help IDs, and error IDs. Accessibility work
in `f546447`/`1f987b8` retained this pre-existing renderer behavior.

**Fix:** `resources/js/components/base/forms/BaseFormRenderer.vue` prefixes field
IDs with a per-instance Vue `useId()`. The existing field shell remains the sole
owner of label/help/error associations. Vue documents these IDs as stable across
server and client rendering: [useId documentation](https://vuejs.org/api/composition-api-helpers.html#useid).

**Verification:** a two-renderer test checks unique IDs and each form's local label
and description associations. It failed with the previous field-name IDs.

### 4. Processing state did not guard form submission — medium

Disabling controls did not guard the form's submit event itself. A submit event
while processing still emitted another mutation request. The disabled-control
hardening in `1f987b8` left this shared transport boundary incomplete.

**Fix:** `BaseFormRenderer.vue` emits `submit` only when `processing` is false.
Existing submit handling and backend validation remain unchanged.

**Verification:** the regression triggers submission during processing, then
reenables the form and verifies one submit. It failed before the guard.

### 5. Public scaffolds rendered authenticated application chrome — high

Both page and CRUD stubs hardcoded `AppLayout`. Public routes permit a null user,
but authenticated navigation requires a user, so a generated public page could
fail during rendering. The scaffold work in `be8eedf`/`e4d20c3` retained this
pre-existing mismatch; the protected-only probe in `f546447` did not exercise it.

**Fix:** the planner now derives layout tokens from the resolved web middleware.
`auth` and `auth:<guard>` select `AppLayout`; guest/public pages select the existing
`MarketingPageLayout`. Breadcrumb declarations and attributes follow that choice.
The same authentication predicate drives generated guest tests. The command
resolves explicitly supplied page-only route profiles without inventing backend
routes or changing the default page prompt flow.

**Verification:** 15 combinations cover page/CRUD/CRUD-API scaffolds with public,
app, guest, custom-default, and guard-specific middleware. The isolated gate now
also generates a public CRUD/API module and server-renders its actual page/layout
with a guest Inertia context.

### 6. Valid identifiers could produce invalid PHP imports — high

Names such as `Model`, `Factory`, `RuntimeException`, `PaginationQueryData`,
`JsonResponse`, and `Controller` passed identifier validation but collided with
imports or declarations in rendered stubs. Generation could succeed while writing
PHP that would not compile. Keyword checks in `be8eedf` and import sorting in
`e4d20c3` did not validate this relationship.

**Fix:** `TemplateRenderer.php` parses rendered PHP and checks class import short
names/aliases against declarations and other imports before the planner writes
files. Validation follows the repository's one-top-level-class-import-per-line
stub convention, then the existing sorter runs. A conflict gives an actionable
name-change error instead of partially creating a module.

**Verification:** all six conflicting module names previously succeeded; tests
now assert failure and the absence of generated module/model files.

### 7. Scaffold whitespace verification excluded new files — low

`git diff --check` ignores untracked files. The isolated scaffold gate introduced
in `e4d20c3`/`f546447` therefore did not check whitespace in newly generated source.

**Fix:** `scripts/verify-generated-module.sh` marks files intent-to-add inside its
temporary checkout before the diff check. It does not stage or modify the source
checkout. The gate includes the public-page fixture described above.

### 8. Browser history retained previous-account page props — high

The existing Inertia configuration disabled history encryption, and account
transitions did not request key rotation. API cache clearing in `be8eedf` and
`1f987b8` therefore left a separate browser history cache untouched. Back navigation
could restore privileged page props after logout. This pre-existing configuration
gap survived all four branch commits.

**Fix:** enable `INERTIA_ENCRYPT_HISTORY` by default in `config/inertia.php` and
`.env.example`; call the canonical `Inertia::clearHistory()` on successful login,
registration, logout, and account deletion, after session changes. The next page
consumes the rotation flag. Document HTTPS/localhost requirements and the explicit
opt-out. This follows [Inertia's history encryption contract](https://inertiajs.com/docs/v3/security/history-encryption).

**Verification:** authentication, registration, and profile tests check encryption
metadata and transition flags; successive page requests verify the flag is
consumed once. Five missing protections were reproduced before the repair.

### 9. Unverified users hit an undefined verification route — high

Laravel's `verified` middleware defaulted to `verification.notice`, while the
application defines `auth.verification.notice`. Following registration to the
dashboard produced a server error. The same path follows email changes, which
`be8eedf` correctly made invalidate verification. The namespacing mismatch existed
before this branch and was missed because earlier tests checked the initial
redirect or visited the verification screen directly.

**Fix:** configure the existing `verified` alias centrally in `bootstrap/app.php`
using `EnsureEmailIsVerified::redirectTo('auth.verification.notice')`. This retains
Laravel's authorization behavior and JSON 403 response without duplicating
middleware or editing every existing/generated route.

**Verification:** registration follows redirects with a fresh auth guard; tests
exercise unverified dashboard/admin web and JSON requests, and the dashboard visit
after a profile email change. All focused authentication/settings checks pass.

### 10. Verified timestamps were discarded by the shared user DTO — medium

Browser testing showed a verified account as unverified. Essentials enables
immutable dates, but `UserViewData::fromModel()` accepted only mutable `Carbon`
instances and replaced every other value with null. `User` annotations also
incorrectly advertised only mutable dates. This predates the branch; the shared
auth/page contract consolidation in `1f987b8` retained the faulty mapping.

**Fix:** `app/Modules/Shared/Data/UserViewData.php` preserves every non-null date
through `CarbonImmutable::instance()`, while `app/Models/User.php` accurately uses
`CarbonInterface` annotations. This repairs shared page props and every consumer
of the canonical user DTO without frontend casts or a parallel data shape.

**Verification:** the profile page test now checks the actual serialized verified
date against the configured date format. It reproduced the null-value defect
before the fix. The browser confirmed the warning is absent for the verified test account.

### 11. Explicit verification updates were wiped on email changes — medium

When updating a user model with both `email` and an explicit `email_verified_at`
timestamp (such as seeders or administrative actions), the model's `updating`
callback unconditionally reset `$user->email_verified_at = null;`, discarding
the explicitly provided verification status.

**Fix:** `app/Models/User.php` guards the invalidation with
`! $user->isDirty('email_verified_at')`. Changing an email without explicitly
providing a verification timestamp resets it to null, while explicit verification
values are preserved.

**Verification:** regression tests in `ProfileUpdateTest.php`, `UserManagementTest.php`,
and `ApiV1UserEndpointsTest.php` cover both implicit invalidation and explicit
timestamp preservation.

### 12. InputError lacked an explicit ID prop contract — low

`BaseFieldShell.vue` passes `:id="`${props.id}-error`"` for assistive technology
linking via `aria-describedby`, but `InputError.vue` did not declare `id` in
`defineProps`, relying on non-prop attribute fallthrough.

**Fix:** `resources/js/components/InputError.vue` declares `id?: string | undefined`
in `defineProps` and binds `:id="id"` directly on the root container.

**Verification:** component rendering and form association tests in
`BaseInputField.test.ts` and `theme.test.ts` pass with full type safety.

### 13. API transport verification parity — medium

Self-deletion prevention and verification reset on email updates were enforced
by shared domain policies and model events, but direct assertions were only
present in web feature tests. `ChannelPatternResolver` also lacked coverage
for enum, boolean, and stringable parameters.

**Fix:** extended `tests/Feature/Api/V1/ApiV1UserEndpointsTest.php`,
`tests/Feature/Users/UserManagementTest.php`, and
`tests/Unit/Realtime/ChannelPatternResolverTest.php` with direct test cases.

**Verification:** all added assertions pass with zero failures.

## Actions Taken

Additional authentication/date files modified:

- `.env.example`, `config/inertia.php`, `bootstrap/app.php`.
- `app/Modules/Auth/Http/Controllers/AuthenticatedSessionController.php`.
- `app/Modules/Auth/Http/Controllers/RegisteredUserController.php`.
- `app/Modules/Settings/Http/Controllers/ProfileController.php`.
- `app/Models/User.php`, `app/Modules/Shared/Data/UserViewData.php`.
- `tests/Feature/Auth/AuthenticationTest.php`.
- `tests/Feature/Auth/RegistrationTest.php`.
- `tests/Feature/Auth/EmailVerificationTest.php`.
- `tests/Feature/Settings/ProfileUpdateTest.php`.
- Generated Wayfinder action/route helpers for the three edited controllers
  (controller source-line metadata only; public URLs and signatures are unchanged).

| Files modified                                                                                  | Exact responsibility                                                                                            |
| ----------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| `resources/js/composables/useApiQuery.ts`                                                       | Reactive cache epoch, mounted-state clearing, synchronous key changes, SSR guards, and mutation context checks. |
| `resources/js/composables/__tests__/useApiQuery.test.ts`                                        | Query/account and asynchronous mutation regression coverage.                                                    |
| `resources/js/components/base/forms/BaseFormRenderer.vue`                                       | Per-instance field IDs and processing-aware submit guard.                                                       |
| `resources/js/components/base/forms/__tests__/BaseInputField.test.ts`                           | Multiple-form associations and submit-state regression coverage.                                                |
| `app/Modules/Shared/Console/Commands/GenerateModuleCommand.php`                                 | Resolve explicit page scaffold layout context.                                                                  |
| `app/Modules/Shared/Support/ModuleGeneration/ModuleScaffoldPlanner.php`                         | Shared authentication predicate and layout tokens for both page stubs.                                          |
| `app/Modules/Shared/Support/ModuleGeneration/TemplateRenderer.php`                              | Pre-write rendered PHP syntax/import validation.                                                                |
| `stubs/module-generation/frontend/page.stub`, `stubs/module-generation/frontend/crud-page.stub` | Consume canonical layout/breadcrumb tokens.                                                                     |
| `tests/Feature/Console/GenerateModuleCommandTest.php`                                           | Layout matrix and atomic rejection of import collisions.                                                        |
| `scripts/verify-generated-module.sh`, `scripts/generated-public-page.test.stub`                 | Protected/public integration probe, guest SSR regression, and new-file whitespace coverage.                     |
| `AGENTS.md`                                                                                     | Enforce the final cache, form, scaffold, and verification contracts for future agents.                          |
| `README.md`, `docs/frontend-automation.md`, `docs/how-to-add-module-page.md`                    | Document observable behavior, scaffold options, and verification scope.                                         |
| `docs/starter-kit-audit.md`                                                                     | Current commit assessment, findings, fixes, evidence, and remaining runtime boundaries.                         |

The architectural repair stays in shared query/form infrastructure and the
canonical scaffold pipeline. No feature-local duplicate cache or layout logic was
introduced. Backend DTOs, enums, route helpers, and realtime payloads remain the
source of truth. New error behavior uses the existing typed mapping contract;
there are no new broad casts or manually duplicated backend types.

## Cleanup

The review preserves intentional entrypoint AST literals, framework forwarding
adapters, typed cache boundary assertions, and historical event snapshots. These
are required contracts, not unused compatibility code. No temporary generated
module belongs in the source tree. Dependency manifests and locks are unchanged. Generated Wayfinder comments reflect
the edited controllers; generated contract shapes remain unchanged.

## Verification Results

Checks ran with PHP 8.4.23 and the existing portable Node 24.20.0/npm 11.19.0
runtime. System Node 24.10.0 does not satisfy the repository engine requirement.

| Check                                         | Result                                                                                                                                                                                                                                                                          |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `composer generate-and-cleanup`               | Passed after repairs, with zero reported errors or warnings.                                                                                                                                                                                                                    |
| `composer qa:check`                           | Passed: Rector, Pint, PHPStan, Prettier, ESLint, and Vue/TypeScript.                                                                                                                                                                                                            |
| `composer test`                               | 184 passed, 1,027 assertions.                                                                                                                                                                                                                                                   |
| `npm run test:unit`                           | 113 passed in 22 files.                                                                                                                                                                                                                                                         |
| `npm run build:ssr`                           | Client and production SSR builds passed.                                                                                                                                                                                                                                        |
| `composer validate --strict`                  | Passed.                                                                                                                                                                                                                                                                         |
| `composer audit --locked`, `npm audit`        | No advisories/vulnerabilities reported.                                                                                                                                                                                                                                         |
| `composer install --dry-run --no-interaction` | Lock/platform validation passed; no dependency changes.                                                                                                                                                                                                                         |
| `composer qa:generated`                       | Passed with clean locked Composer/npm installs, protected/public scaffolds, both builds, all static checks, 185 PHP tests (1,046 assertions), and 116 frontend tests in 25 files, including the guest public-page SSR test.                                                     |
| Browser                                       | Home/login SSR and hydration; unique control IDs and labels; checkbox interaction; rejected dummy login with linked validation feedback; authenticated dashboard/settings; logout followed by Back redirected to login; verified account no longer shows an unverified warning. |
| Generated artifacts                           | Wayfinder was regenerated for controller source-line changes; URLs, signatures, DTO shapes, and enums are unchanged. Final regeneration produces no unstaged drift.                                                                                                             |
| Whitespace                                    | Tracked diff checks pass; the isolated gate also checked new files. Final staged and unstaged checks pass.                                                                                                                                                                      |

The scaffold gate covers the final generator, cache, and form changes. Later
history, verification-route, and date repairs were validated by the final source
suite and browser checks above. Its temporary checkout was removed. Browser
account tests used a disposable SQLite database and a separate session cookie;
application records were not changed. The disposable database and fixture were removed, and the audit HTTP/SSR processes were stopped. The final profile check had no console
warnings/errors. An earlier tab reported an extension message-channel error,
without an application failure.

Evidence logs are local, ignored files under `storage/logs/maintainer-*`: regression
before/after runs, `maintainer-generation.log`, `maintainer-final-gates.log`,
`maintainer-dependencies.log`, and `maintainer-generated-final.log`.

## Residual Risks and Merge Readiness

Local automated checks do not certify the deployment environment. Linux CI must
pass for the committed result. Live Reverb delivery, production PM2 restarts,
deployment CSP/origin settings, real session transitions across tabs, and load
behavior require their respective runtime environments.

Encrypted history requires HTTPS or localhost. Existing explicit environment overrides
that disable encryption remain effective; older plaintext browser entries created
before this change are not retroactively encrypted.

A cache reset cannot cancel a callback already executing or undo a request already
accepted by the server. Consumers with asynchronous callbacks must scope any work
they start themselves. Full cache clears reset mounted values and require an
explicit refresh or a key/enabled transition to fetch again.

All thirteen identified issues are repaired, with no known unresolved code blocker.
The complete fix set is staged and ready to commit after successful final verification. Merge remains
conditional on the required Linux CI checks for that commit; no commit, push, or
merge was performed by this audit.
