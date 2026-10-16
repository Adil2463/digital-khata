# AGENTS.md

## Start Here

Read the sections relevant to the task, then trace their reference implementations before editing. Keep a short record of findings, changed contracts, and remaining verification during broad tasks.

| Task                          | First implementation to inspect                                                           | Verification                                                   |
| ----------------------------- | ----------------------------------------------------------------------------------------- | -------------------------------------------------------------- |
| CRUD, requests, authorization | `app/Modules/Users`, `tests/Feature/Users`, `tests/Feature/Api`                           | PHP suite, generation                                          |
| Forms, tables, themes         | `resources/js/components/base`, `resources/js/lib`, `resources/js/modules/users`          | Vue typecheck, Vitest, client/SSR build                        |
| Realtime and mutations        | `app/Modules/Shared/Realtime`, `app/Modules/Users/Listeners`, `resources/js/lib/realtime` | Realtime PHP/Vitest suites; live Reverb when relevant          |
| Discovery or scaffolding      | `app/Modules/Shared/Support`, `stubs/module-generation`                                   | Generator tests plus `composer qa:generated`                   |
| Dependencies and CI           | `composer.json`, `package.json`, lock files, `.github/actions/setup-project`              | Locked audits, clean install for dependency changes, full gate |

`composer generate-and-cleanup` is the mandatory static completion gate. Run it separately from builds and tests that regenerate artifacts. Then run the affected suites and builds. Do not count passing static checks as browser, deployment, or load-test verification.

## Purpose

This repository is a modular Laravel + Inertia + Vue starter kit with backend-owned contracts, strict typing, realtime support, and generator-backed conventions.
The backend is the source of truth for DTOs, enums, route/action helpers, and realtime contracts.
Agents must keep the codebase internally consistent, remove redundancy, and deliver complete, production-quality implementations with no partial work left behind.

## Source Of Truth Hierarchy

When guidance conflicts, resolve it in this order:

1. Current runtime behavior and code in the repository.
2. Generated artifacts that are owned by backend contracts.
3. `AGENTS.md`.
4. Secondary docs such as `README.md` and files under `docs/**`.

If code and docs disagree, trust the code, then fix the docs in the same task when relevant.

## Non-Negotiable Engineering Bar

- Think through the full impact of every change before editing code.
- Verify the current implementation first; never preserve stale assumptions from docs, habits, or previous starter-kit conventions.
- Understand the full requirement and the full existing implementation before changing anything; do not optimize locally while missing a broader contract, dependency, or workflow constraint.
- Prefer the simplest design that is correct, extensible, testable, and aligned with existing project patterns.
- Continuously align the codebase to global patterns, standards, and best practices across backend, frontend, generated artifacts, and tests instead of letting module-local exceptions accumulate.
- Every meaningful change should reduce mismatch, incomplete logic, duplicate behavior, weak typing, and ad hoc structure where it touches the system.
- Structure code for maximum cleanliness, reusability, maintainability, strict type safety, extensibility, performance, modularity, robustness, and long-term developer experience.
- Prefer the solution with the fewest necessary touch points and the strongest shared abstraction, but never at the cost of correctness, clarity, or alignment with current architecture.
- Preserve working functionality while refactoring. Cleanup is expected to improve consistency and safety without introducing regressions or speculative rewrites.
- Maintain excellent DI: prefer constructor injection, readonly dependencies, explicit typed inputs, and thin transport layers.
- Maintain excellent DX: preserve predictable file placement, generated contract flows, route/action helpers, and shared abstractions instead of ad hoc patterns.
- Maintain excellent performance: avoid duplicate scans, redundant queries, unnecessary rerenders, unnecessary network calls, and unnecessary abstractions.
- Remove dead code, duplicate logic, stale comments, unused scaffolding, and compatibility shims when they are no longer justified.
- Never leave a feature half-finished. If a change requires backend, frontend, generated artifacts, tests, docs, or cleanup, complete all of them in the same task.
- Match the existing architecture precisely. If the architecture must change, update this file in the same change.
- No unnecessary code comments. Code should be self-documenting. Use comments only to explain why something is done, not what is done.

## Mandatory Workflow

1. Inspect the existing implementation, affected modules, related tests, and generated-contract flow before making changes.
2. Confirm the actual requirement, invariants, and user-visible behavior that must remain correct before choosing an implementation approach.
3. Reuse existing module services, requests, DTOs, queries, commands, handlers, responders, composables, and base UI primitives before introducing anything new.
4. Choose the most efficient clean change that improves global consistency and minimizes future touch points, not just the shortest local patch.
5. Keep changes focused, typed, and module-local unless the concern is genuinely cross-cutting.
6. Regenerate artifacts whenever backend-owned contracts, routes, channels, providers, listeners, or enums change.
7. Run the required quality gates after every change and treat `composer generate-and-cleanup` as a mandatory zero-error, zero-warning completion gate.
8. Update `AGENTS.md` whenever architecture, workflows, or enforcement rules change.
9. Do not stop at analysis. Deliver the finished implementation, verification, and cleanup unless the user explicitly redirects you.

## Global Alignment Standard

- Backend, frontend, generated contracts, tests, and docs must converge on one consistent canonical implementation for each concern.
- Do not leave known mismatch, duplicate semantics, inconsistent naming, incomplete logic, or parallel contract shapes in place when the current task touches them.
- Prefer direct cleanup over compatibility shims unless persisted data or external consumers require an explicit transition.
- Use backend-owned DTOs, enums, route helpers, realtime payloads, and generated contracts as the canonical contract surface. Do not redefine them manually in frontend or feature-local code.
- Shared concerns must use shared abstractions. Module-specific concerns must remain module-local. Do not duplicate cross-cutting behavior in feature modules and do not prematurely move feature-specific logic into shared layers.
- Refactors must improve consistency across modules, not create a stronger pattern in one module while leaving newly introduced divergence elsewhere in the touched scope.
- Any cleanup that changes structure must keep strict typing intact end to end: request DTOs, handlers, resources, generated types, composables, page props, tests, and runtime parsing.
- When choosing between alternatives, prefer the one that most clearly strengthens long-term maintainability, explicit contracts, and safe extension paths.

## Agent Workflow Protocol (MANDATORY)

Before writing ANY code, agents MUST follow this protocol:

1. Understanding Phase (REQUIRED)
    - Read First: Never assume. Read all related files, understand the full context.
    - Trace Dependencies: Map all files, services, events, and components that will be affected.
    - Validate Requirements: Confirm the exact requirement, expected runtime behavior, generated-contract implications, and non-functional constraints before designing a change.
2. Planning Phase (REQUIRED)
    - Design First: Outline the solution architecture before coding.
    - Impact Analysis: List all files that need changes (backend, frontend, tests, docs).
    - Alignment Check: Compare the intended change to existing global patterns and identify any touched mismatch, duplicate logic, or incomplete contract that should be cleaned up in the same pass.
3. Implementation Phase
    - One Change at a Time: Make focused, atomic changes.
    - Follow Patterns: Match existing conventions exactly.
    - Raise The Bar: Leave touched code more consistent, more strictly typed, and less duplicated than you found it.
4. Verification Phase
    - Run Quality Gate: `composer generate-and-cleanup` after every change.
    - Zero-Warning Rule: Do not consider the task complete while `composer generate-and-cleanup` reports errors or warnings that can be resolved within the repository.
5. Completion Criteria
    - All tests pass, no dead code/TODOs remain, and `AGENTS.md` is updated if applicable.

## Change-Impact Checklist

For every non-trivial change, explicitly verify all affected layers before considering the task complete:

- backend contracts
- generated artifacts
- frontend consumers
- route/action helper usage
- authorization and policies/gates
- realtime contracts and subscriptions
- database schema, indexes, and query behavior
- tests
- docs
- stale code removal

## Current Stack

- PHP: `^8.4`
- Laravel: `^13.36`
- Inertia Laravel: `^3.5`
- Inertia client (`@inertiajs/vue3`, `@inertiajs/vite`): `^3.9`
- Reverb: `^1.12`
- Sanctum: `^4.3.3`
- Wayfinder: `^0.1.21` (sole route surface; Ziggy is deliberately not installed)
- Spatie Laravel Data: `^4.23`
- Spatie TypeScript Transformer: `^3.3`
- Pest: `^5.3` with Laravel `^5.0` and PHPStan `^5.2` plugins (PHPUnit 13 engine)
- PHPStan/Larastan: `^2.3` / `^3.13` at level 9 with official strict and deprecation rules
- Rector: `^2.7`
- Vue: `^3.5.44`
- VueUse: `^15.0`
- TypeScript checker: `7.0.2` through `typescript-native-bridge` (`6.0.3-bridge.18.tsgo.7.0.2`), retaining the classic compiler API for Vue and ESLint
- Vitest: `^5.0`
- Vite: `^8.3` (Rolldown bundler)
- `laravel-vite-plugin`: `^3.2`
- Tailwind CSS: `^4.3`
- Pinia: `^4.0`
- Icons: Iconify collections compiled by `unplugin-icons` `^24.0`; runtime icon component packages are deliberately not installed
- Node: `>=24.15.0`
- npm: `>=11.2.1`
- Package manager: `npm` ONLY. This is enforced by `ensure-node-env.js`. Do not use yarn, pnpm, or bun.
- `.npmrc` enables `engine-strict` so installs reject incompatible dependency runtimes. Node 24.15+ is required by jsdom 30; CI uses the latest Node 24 release.
- npm's `allowScripts` has no approved dependency scripts. VueUse 15 no longer needs the `vue-demi` adapter; review any new install scripts individually.
- `composer.lock` and `package-lock.json` are committed application contracts; CI and local reproducible installs must honor them.
- Published Sail Docker contexts are limited to `docker/8.4` and `docker/8.5`, matching the Composer PHP constraint.

### Version Constraints Worth Knowing

- Wayfinder 0.1.21 has platform-dependent newline cleanup. `cweagans/composer-patches` applies `patches/wayfinder-portable-newlines.patch` during installation so Artisan, Composer, and Vite emit identical LF helpers. Commit `patches.lock.json` with the patch; do not edit vendor files or format generated helpers separately. Remove the patch after an upstream release passes the portability regression tests on Windows and Linux.
- After changing a Composer patch, run `composer patches-relock`, `composer patches-repatch`, and the generation/quality gates. CI vendor caches include the patch lock and patch files so an older unpatched vendor directory cannot be restored.
- Wayfinder 0.1.21's method union predates Laravel 13's `QUERY` verb. `patches/wayfinder-query-method.patch` adds it to the generated type contract. Remove this patch when upstream supports `QUERY` and the query-route regression and Vue typecheck pass.
- `typescript` is an exact npm alias to `typescript-native-bridge`, with `overrides.typescript = "$typescript"` so Vue, ESLint, and Prettier share the TypeScript 7 checker and classic API adapter. The version's `6.0.3` prefix identifies the API facade; `tsgo.7.0.2` identifies the checker. Do not replace it with stock `typescript@7` until Vue and the compiler-API consumers support it. Never use forced installs or disable strict checks to upgrade.
- Keep the bridge exactly pinned and revalidate compiler diagnostics, formatting, lint, client/SSR builds, and clean `npm ci` when changing it. `resources/js/types/__tests__/compiler.test.ts` checks TypeScript 7 Unicode inference and rejection of invalid Vue props, template methods, and nullable backend fields.
- The bridge supports Windows/macOS and glibc Linux (2.31+), including the Ubuntu 24.04 Sail images and Ubuntu CI. Alpine/musl is unsupported; run frontend tooling in a glibc build stage. Platform binaries come from its locked optional dependencies; do not omit optional dependencies during installation.
- VS Code should use the workspace SDK in `node_modules/typescript/lib` with Vue - Official. Keep the classic language service for Vue plugin support; see `docs/frontend-automation.md` for editor setup and the bridge removal criteria.
- `concurrently` 10.0.6 pins the fixed `shell-quote` 1.12.0, so no security override is needed.
- `fontaine` stays on `^0.8.2`, the latest release supported by `laravel-vite-plugin` 3.2's optional `^0.8.0` peer. Upgrade it to 1.x when the plugin supports that peer; keep metric-matched self-hosted font fallbacks and strict peer checks.
- `optionalDependencies` pin the Linux x64 native binaries used by CI/Docker. Vite 8 bundles with Rolldown, so the binding is `@rolldown/binding-linux-x64-gnu` (not `@rollup/rollup-*`).
- Those three entries must use exact versions that match the resolved core packages (`rolldown`, `lightningcss`, `@tailwindcss/oxide`). A caret range can hoist a newer binding than the core package expects and break the Linux build. Re-check them after any Vite or Tailwind bump.
- Vite 8.3 uses Lightning CSS 1.33, while Tailwind 4.3.3 pins 1.32. Keep each core's matching binding in the lockfile; npm may install Tailwind's binding beneath its older core.
- ESLint composes `typescript-eslint`'s recommended configs with `eslint-plugin-vue` and `vue-eslint-parser` directly. Preserve the Vue script/template parser, `vue/block-lang`, and every module/import guard. `@vue/eslint-config-typescript` was removed because its `fast-glob` dependency pulls in unpatched vulnerable `braces`; no project file scan is needed for this non-type-aware lint configuration.

## Current Runtime And Tooling

- `composer setup` is Laravel's first-run script: install both dependency graphs, create `.env`, generate the key, migrate, and build assets.
- `.env.example` keeps Laravel's `APP_URL=http://localhost`, and `php artisan serve` keeps its default `127.0.0.1:8000`, which Sanctum's default stateful domains already cover. When serving on another host or port, set `APP_URL` to that origin so first-party API requests stay stateful.
- `composer dev` starts:
    - `php artisan serve`
    - `php artisan queue:listen --queue=realtime,high,default --tries=1 --timeout=0`
    - `php artisan pail --timeout=0`
    - `npm run dev`
    - `php artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=127.0.0.1 --no-interaction`
- `composer dev:ssr` builds SSR assets, then starts:
    - `php artisan serve`
    - `php artisan queue:listen --queue=realtime,high,default --tries=1 --timeout=0`
    - `php artisan pail --timeout=0`
    - `php artisan inertia:start-ssr`
    - `php artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=127.0.0.1 --no-interaction`
- `pm2.config.cjs` currently manages production-style queue workers, Reverb, the Inertia SSR server, and the scheduler. PM2 starts Reverb without flags so it binds to `REVERB_SERVER_HOST`/`REVERB_SERVER_PORT` from `.env`; shell-expanded flags would ignore `.env`.
- `hosting/nginx_config` is the single-server nginx setup documented in `docs/deployment.md`. `app.conf` (behind a TLS-terminating proxy) and `app-https.conf` (nginx terminates TLS) both include `snippets/app-locations.conf`, which owns all routing. Reverb shares the domain: only `Upgrade: websocket` requests under `/app/` reach it, because Reverb's `/app/{key}` endpoint shares the prefix with the application's `/app/*` pages. Never route by a literal app key. Only `public/index.php` executes. Keep the routing change and `docs/deployment.md` in the same change; `nginx -t` with request checks is the verification, since no automated suite covers these files.
- `config/trustedproxy.php` feeds Laravel's global `TrustProxies` middleware from `TRUSTED_PROXIES`. Leave it unset when clients connect directly; behind a TLS-terminating proxy it must name that proxy, or generated URLs and asset links use `http://`.
- Run one scheduler per host. Long-lived services restart after a successful exit so deployment commands such as `queue:restart` do not leave workers stopped. Multiple scheduler hosts require Laravel's shared-cache scheduling locks.
- SSR is served two different ways and both are wired up:
    - Development: `@inertiajs/vite` exposes `/__inertia_ssr` on the Vite dev server and `inertia-laravel` routes to it automatically while Vite is hot. `composer dev` therefore renders pages server-side with HMR and no extra process.
    - Production / `composer dev:ssr`: `npm run build:ssr` emits `bootstrap/ssr/app.js`, which Inertia's bundle detector finds without configuration, and `php artisan inertia:start-ssr` serves it as a single Node process.
- `INERTIA_SSR_ENABLED` in `.env.example` toggles both paths.
- `vite.config.ts` excludes `storage`, `vendor`, and the build output directories from the dev server's file watcher. Watching them creates tens of thousands of watchers; on Windows that blocked the dev server for 20 seconds to several minutes on start, long enough for the SSR warm-up to time out and for `php artisan serve` to hit its 30-second limit. Keep new non-frontend directories with many files out of the watcher the same way. Vite's dependency optimizer discovers every bare import in its first pass, so no `optimizeDeps.include` list is maintained.

## Canonical Architecture

### Backend

- Application bootstrapping is defined in `bootstrap/app.php`.
- `bootstrap/providers.php` only lists:
    - `App\Providers\AppServiceProvider`
    - `App\Providers\AuthServiceProvider`
    - `App\Providers\TypeScriptTransformerServiceProvider`
- Module providers are not added there manually; they are auto-registered during app boot through `App\Modules\Shared\Support\ModuleRegistry::providerClasses(...)`.
- Web middleware appended in `bootstrap/app.php`:
    - `App\Http\Middleware\HandleAppearance`
    - `App\Http\Middleware\HandleInertiaRequests`
    - `App\Http\Middleware\SecurityHeaders`
    - `Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets`
- Guest redirects are configured centrally in `bootstrap/app.php` to `route('auth.login.create')`, and authenticated users visiting guest-only routes are redirected to `route('app.dashboard')`. Laravel's own fallback looks for a route named `dashboard` or `home`, which the namespaced route names do not provide.
- The `verified` middleware alias is configured there with Laravel's `EnsureEmailIsVerified::redirectTo('auth.verification.notice')`. Keep this central default so existing and generated protected routes share the namespaced verification prompt; JSON requests retain Laravel's 403 response.
- Inertia history encryption defaults to enabled (`INERTIA_ENCRYPT_HISTORY=true`) and requires HTTPS or localhost. Login, registration, logout, and account deletion call `Inertia::clearHistory()` after changing the session so the next page rotates the browser history key. Query-cache clearing does not replace this protection.
- `statefulApi()` enables Sanctum session authentication and CSRF protection for API requests from configured first-party origins. Keep `SANCTUM_STATEFUL_DOMAINS` aligned with deployed frontend hosts, including ports. External clients use bearer tokens. Authentication regressions must exercise persisted cookies or real tokens; `actingAs` alone cannot verify middleware wiring.
- Events are registered from `app/Listeners` and module-discovered listener directories via `ModuleRegistry::listenerDirectories(...)`.

### Module Discovery

- Canonical discovery source: `app/Modules/Shared/Support/ModuleRegistry.php`
- Cache version: `3` (auto-invalidated when version changes)
- Cached manifest path: `bootstrap/cache/modules.php`
- Discovery payload currently includes:
    - web routes
    - api routes
    - gate files
    - policy files
    - channel files
    - listener directories
    - module provider files
- Current discovery priority order:
    - web routes: `Marketing`, `Auth`, `Dashboard`, `Settings`, `Users`
    - api routes: `Api/V1`
    - gate files: `Users`
    - policy files: `Users`
    - channel files: `Shared`, `Users`
    - listener directories: `Users`
    - provider files: no explicit priority list
- Production HTTP runtime trusts the cached manifest when available.
- Console and non-production runtimes can rescan when the manifest is missing or stale.
- Do not introduce separate ad hoc route, gate, channel, listener, or provider discovery logic outside the registry unless the architecture is intentionally changed and this file is updated.

### Current Module Layout

- Module root: `app/Modules`
- Current modules:
    - `Marketing` - Public marketing pages
    - `Auth` - Authentication (login, register, password reset, email verification)
    - `Dashboard` - Authenticated app dashboard
    - `Settings` - User settings (profile, password, appearance)
    - `Users` - Admin user management with realtime
    - `Api/V1` - API v1 endpoints
    - `Shared` - Shared infrastructure and utilities
- Shared domain primitives remain in:
    - `app/Models/User.php`
    - `app/Enums/UserRole.php`

### Backend Ownership Rules

- Module-specific backend code must stay under `app/Modules/<Module>/**`.
- Keep these inside the owning module:
    - controllers
    - requests
    - data DTOs
    - queries
    - commands
    - handlers
    - policies
    - gates
    - broadcast events
    - notifications
    - channel callbacks
    - listeners
    - support classes
    - resources
    - manifests
    - events (domain and broadcast)
- Only truly shared domain primitives belong in `app/Models/**` and `app/Enums/**`.
- Model date annotations use `CarbonInterface` to cover mutable and immutable casts. DTO boundaries normalize non-null dates with `CarbonImmutable::instance`; never discard an immutable date with an `instanceof Carbon` check. Essentials enables immutable dates by default.
- If reuse spans multiple modules, move the code to `app/Modules/Shared/**` instead of duplicating it.
- Do not place module-only code under global shared locations.
- Backend event listeners under `app/Modules/**/Listeners/**` are auto-discovered through Laravel event discovery. Do not manually register module listener classes in `AppServiceProvider`.

### Shared Backend Infrastructure

- Shared page responder: `app/Modules/Shared/Http/Responders/PageResponder.php`
- Shared API responder: `app/Modules/Shared/Http/Responders/ApiResponder.php`
- Shared request DTO base classes:
    - `app/Modules/Shared/Http/Requests/DataRequest.php`
    - `app/Modules/Shared/Http/Requests/DataFormRequest.php`
    - `app/Modules/Shared/Http/Requests/DataQueryRequest.php`
- Shared authenticated actor resolver:
    - `app/Modules/Shared/Auth/RequestActor.php`
- Shared mutation context infrastructure:
    - `app/Modules/Shared/Mutations/MutationMetadata.php`
    - `app/Modules/Shared/Mutations/MutationContext.php`
- Shared realtime infrastructure:
    - `app/Modules/Shared/Realtime/**`
    - `app/Modules/Shared/Enums/SharedRealtimeChannel.php`
    - `app/Modules/Shared/Routes/channels.php`
- Current auto-discovered shared module provider:
    - `app/Modules/Shared/Providers/ModuleServiceProvider.php`

### Request / Controller / Handler Conventions

- Every PHP file must declare `strict_types=1`.
- Console commands use Laravel's typed `input()->string(...)->toString()` and `input()->boolean(...)` readers for signature-defined strings and flags.
- Transport-layer input must be typed through request DTOs.
- Prefer extending `DataFormRequest` for validated form payloads.
- Prefer extending `DataQueryRequest` for query payloads so trimming, defaults, pagination, and enum casting stay centralized.
- `toDto()` comes from `DataRequest`; do not hand-roll ad hoc array extraction when DTO hydration fits.
- Controllers must stay thin.
- Use `RequestActor::from($request)` whenever controller logic requires the authenticated app user.
- Prefer module-local `Queries` for reads and `Commands` for writes.
- When the same use case is exposed through both web and API transports, keep orchestration in module-local `Handlers`.
- The current `Users` module is the reference pattern for shared orchestration through `UserQueryHandler` and `UserCommandHandler`.
- Use `PageResponder` for Inertia pages and `ApiResponder` for JSON/resource responses instead of ad hoc response building.

### Mutation / Event / Realtime Pattern

- When command-side behavior fans out into audit logging, notifications, or realtime, emit one mutation context object and let listeners consume it.
- The current reference flow is:
    - `UserActionContext::fromRequest(...)`
    - `MutationMetadata::fromRequest(...)`
    - `MutationContext`
    - `UserManagementEvent`
    - listeners under `app/Modules/Users/Listeners/**`
- Domain events should remain separate from broadcast events.
- `UserManagementEvent` implements `ShouldDispatchAfterCommit`. Dispatch it after persistence succeeds; rolled-back transactions must not emit audit, notification, or realtime side effects. `UserActionContext` snapshots actor and target models so later changes within the transaction cannot rewrite an earlier event. Deleted-user broadcasts carry `user: null`.
- Listeners translate domain events into realtime broadcasts and notifications.
- Realtime events should extend `app/Modules/Shared/Realtime/Events/RealtimeEvent`.
- `RealtimeEvent` currently broadcasts:
    - on queue `realtime`
    - with `afterCommit = true`
- Realtime dispatch should go through `App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher`.

### Canonical Reference Implementations

When adding similar behavior, inspect and follow the nearest established reference instead of inventing a new pattern:

- `Users` module:
    - query/command/handler orchestration
    - mutation context flow
    - realtime broadcasts and notifications
    - server-driven admin listing pages
- `Settings` module:
    - typed form request DTO hydration
    - page responder usage
    - schema-driven forms
- `Api/V1` module:
    - API responder usage
    - resource responses
    - shared request DTO reuse across transports
- `Shared` module:
    - module registry/discovery
    - shared realtime infrastructure
    - shared request/responders/auth helpers

### Frontend

- `resources/js/app.ts` is the single entry for both the client and SSR, matching Inertia v3's default. `@inertiajs/vite` auto-detects it as the SSR entry, and `laravel()` names it as the `ssr` input so the stylesheet stays out of the SSR build. Do not reintroduce a separate `ssr.ts` or a manual `createServer` wrapper.
- `createInertiaApp(...)` must stay a bare top-level statement: the plugin rewrites it into the render function for the dev SSR endpoint and the production `createServer` boot. Its `pages` shorthand must stay an inline object literal, because a static AST transform compiles it into an `import.meta.glob` resolver over `resources/js/modules/**/*.vue`. The `transform` strips the leading `modules/` from the backend-supplied component name (`modules/users/pages/Index`).
- Shared page props are typed through Inertia v3's `InertiaConfig.sharedPageProps` augmentation in `resources/js/types/globals.d.ts`, bound to the generated `SharedPageData`. `usePage()`, `$page`, and `createInertiaApp` are therefore typed without a type argument; do not cast individual shared props.
- `globals.d.ts` also declares the `VITE_*` variables the frontend reads, and enables Vite's `strictImportMetaEnv`, so reading an undeclared `import.meta.env` key is a type error instead of `any`. Declare a new client variable there when adding it to `.env.example`.
- `setup` in `app.ts` builds the Vue app for both renders, so they cannot drift apart. It renders `resources/js/components/AppRoot.vue` around the Inertia page; `AppRoot` owns the global toaster and the query-cache reset on identity changes. Register new plugins in `setup` and new root-level components in `AppRoot`.
- `setup` uses `createSSRApp` on the server and for roots Inertia marks with `data-server-rendered`, and `createApp` otherwise, so `INERTIA_SSR_ENABLED` can be toggled without a hydration mismatch.
- `setup` calls VueUse's `provideSSRWidth` with the desktop breakpoint. The server cannot measure a viewport, so media queries (the sidebar's mobile check) resolve against that width on the server and during hydration, then switch to the real viewport after mount. Without it, narrow screens hydrate a different sidebar tree and every later `useId()` shifts, breaking label and `aria-describedby` targets in production.
- Nothing request-scoped may be written to a module-level binding or to `globalThis`. The SSR process is long-lived and serves concurrent requests, so a per-request global is overwritten by whichever request rendered last. Current applications of this rule:
    - Pinia is created per app instance in `setup`
    - `useToast` drops toasts under SSR
    - `useApiQuery` never reads, writes, or fetches into its module-level cache or in-flight request map under SSR
    - `useAppearance` keeps only a client override at module scope, seeded from the `appearance` page prop and written solely from a browser interaction
- Code at the top level of `app.ts` runs in both environments. Guard browser globals with `import.meta.env.SSR`, which Vite replaces at build time.
- `SecurityHeaders` generates the per-request CSP nonce with Laravel's `Vite::useCspNonce()`, so `@vite` adds it to every tag it renders. The root view exposes `Vite::cspNonce()` in a `meta[name="csp-nonce"]` tag, and `app.ts` passes it to Inertia so its injected style elements satisfy the policy.
- The root Blade template uses `data-inertia` (not `inertia`) on head elements, per Inertia v3.
- `resources/css/app.css` is a Vite entry of its own (`vite.config.ts` `input`, first in the `@vite` array) and must never be imported from `app.ts`. SSR ships a fully rendered document, so the browser paints as soon as the HTML lands; CSS reaching it through the JS module graph would render that markup unstyled and reflow when the bundle evaluated. As a separate entry it is a render-blocking `<link rel="stylesheet">` in dev and production alike — the Vite dev server serves it as real `text/css` because a `<link>` sends `Accept: text/css`.
- The color scheme is server-rendered. `HandleAppearance` normalizes the `appearance` cookie through `App\Enums\Appearance` and shares it with the root view, which puts `class="dark"` on `<html>` directly; there is no boot script and nothing for the client to re-apply. `HandleInertiaRequests` shares the same value as a page prop so `useAppearance` seeds the appearance UI from it and hydrates without a mismatch. `useAppearance` holds only a client override (`null` until the visitor toggles), which is what keeps this module-level state out of the shared SSR process.
- Nothing that renders into markup may be nondeterministic. `Math.random()`, `Date.now()`, and `crypto.randomUUID()` produce one value in the SSR process and a different one during hydration, so Vue reports a mismatch and the server's value is the one that stays in the DOM. Use `useId()` for generated element ids and `url(#…)` references (`PlaceholderPattern`, `SidebarMenuSkeleton`); it is stable across both renders. This applies to vendored `components/ui/**` primitives too — upstream shadcn-vue does not assume SSR.
- `SecurityHeaders` allows the `http:`/`https:` scheme sources in `style-src` and `font-src` only when `App::isLocal()`, because the dev stylesheet and fonts are served by the Vite dev server origin rather than `'self'`. Outside local, every asset, fonts included, is same-origin and `<style>` elements require the nonce. `style-src-attr 'unsafe-inline'` applies everywhere: Vue renders `v-show` and bound styles (such as the sidebar's width variables) as `style` attributes in server-rendered markup, a nonce cannot cover an attribute, and attributes cannot execute script.
- Fonts are self-hosted through laravel-vite-plugin's `fonts` option in `vite.config.ts`, which is the only place a font family is declared. Instrument Sans ships as one variable latin woff2 (weights 400–700) read from the locked `@fontsource-variable/instrument-sans` package, so builds need no network. `@fonts` in the root view preloads it (also sent as an HTTP `Link` header) and inlines the `@font-face` rules under the CSP nonce. The optional `fontaine` peer generates a metric-matched `Instrument Sans fallback` face so text does not shift when the font arrives. `theme.css` maps Tailwind's `--font-sans` to the generated `--font-instrument-sans` variable, which already carries the fallback stack. To add a family, add a definition there and reference its variable from `theme.css`; never link a third-party font stylesheet.
- Feature pages live in `resources/js/modules/**/pages`.
- Feature forms live in `resources/js/modules/**/forms`.
- Feature-specific components live in `resources/js/modules/**/components`.
- Feature-specific contracts live in `resources/js/modules/**/contracts`.
- Feature-specific composables live in `resources/js/modules/**/composables`.
- Feature-specific helpers live in `resources/js/modules/**/helpers`.
- Shared layers:
    - `resources/js/components/**`
    - `resources/js/layouts/**`
    - `resources/js/composables/**`
    - `resources/js/config/**`
    - `resources/js/lib/**`
    - `resources/js/utils/**`

### Frontend UI Layering

- `resources/css/theme.css` is the sole owner of raw visual values: semantic colors, light/dark mappings, status tones, radii, shadows, glass/overlay values, gradients, and reduced-motion defaults.
- `resources/js/lib/theme.ts` is the sole owner of reusable class recipes and typed style variants for shared controls, surfaces, dialogs, sheets, menus, inputs, tables, pagination, feedback, status, navigation, and animation.
- `resources/js/components/ui/**` = low-level primitive wrappers (reka-ui, icons, etc.).
- UI primitives (`resources/js/components/ui/**`) are built using shadcn-vue style components on top of Reka UI.
- Base UI component names use the `Ui*` prefix (for example `UiButton`, `UiInput`, `UiSelect`, `UiCard`, `UiDialog`). Use these existing primitives before building custom elements.
- `resources/js/components/base/**` = reusable app-level building blocks (`Base*`).
- `resources/js/modules/**` = feature-specific screens, dialogs, tables, and contracts.
- Do not place feature-specific UI in `resources/js/components/**`.
- Prefer composing `Base*` components rather than rebuilding common structures.
- `appTheme` is auto-imported but must be aliased in `<script setup>` (`const theme = appTheme`) before a template uses it. `vue-tsc` cannot resolve an auto-imported symbol that appears only in markup. UI primitives import the theme explicitly and are included in typechecking and ESLint.
- Forward props to a reka-ui primitive with `useForwardedProps` / `useForwardedPropsEmits` from `@/lib/forward-props`, narrowing with `reactiveOmit` where a prop is consumed locally. reka-ui already drops undefined values while forwarding; those adapters only restate that contract for `exactOptionalPropertyTypes`, so they cost nothing at runtime. Never assert `as Partial<...>` or `as Record<string, unknown>`: both hide missing required props, and re-filtering an already-forwarded object duplicates work reka-ui did.
- `omitUndefinedProps` remains the tool for sanitising an object the component builds itself. The forwarding adapters read the calling component instance, so they only ever forward that component's own props.
- Icons come only from Iconify through `unplugin-icons` using the `Icon*` component prefix. Use auto-resolved tags such as `<IconLucideChevronLeft />` in templates and virtual imports such as `~icons/lucide/chevron-left` when a component value is required in TypeScript. Application chrome uses the `lucide` collection; a multi-word collection needs the explicit `<Icon-<collection>:<name> />` form, as in `<Icon-icon-park-outline:system />`.
- Icon-bearing contracts use Vue's generic `Component` type so they remain collection-agnostic.
- `components.json` points the shadcn-vue CLI at `resources/css/theme.css` and declares no `iconLibrary`. Newly vendored primitives must be rewritten onto `appTheme` recipes and `~icons/*` before they are committed.
- Do not introduce raw color literals, Tailwind palette colors, local `<style>` blocks, duplicated shared recipes, or runtime icon component packages in frontend source. Extend the canonical theme contracts instead.
- `stubs/module-generation/frontend/**` emits real frontend source, so it follows every rule above. Generated icons use `~icons/*` virtual imports, and `resources/js/lib/__tests__/theme.test.ts` enforces the theme contracts across both `resources/js/**` and the stubs: no raw colors or palette utilities, no local `<style>` blocks, no runtime icon packages, no literal re-declaration of a canonical recipe, and no bare `appTheme.` inside a template.

### Frontend Automation Contracts

- Canonical auto-import config: `frontend-auto-import.config.mjs`
- The following files must stay aligned with that config:
    - `vite.config.ts`
    - `vitest.config.ts`
    - `eslint.config.js`
- Auto-imported directories currently include:
    - `resources/js/composables/**`
    - `resources/js/stores/**`
    - `resources/js/lib/**`
    - `resources/js/utils/**`
    - `resources/js/modules/**/composables/**`
    - `resources/js/modules/**/helpers/**`
- Module-local `forms/**` and `contracts/**` are not auto-imported.
- Inertia owns routing; do not enable the Vue Router auto-import preset without a Vue Router runtime. Type-only imports such as `Method` remain explicit.
- `frontend-auto-import.config.mjs` owns two contracts:
    - symbol auto-import (`autoImportDirs`, `autoImportImports`, restricted paths/patterns)
    - component auto-registration (`componentAutoImportOptions`, `inertiaComponentResolver`, `iconComponentPrefix`)
- Vue components are auto-registered from:
    - `resources/js/components`
    - `resources/js/layouts`
    - `resources/js/modules`
- `vitest.config.ts` installs the same `unplugin-auto-import`, `unplugin-vue-components`, and `unplugin-icons` setup as `vite.config.ts`, so a mounted component resolves auto-imported symbols and `Ui*`, `Base*`, and module components exactly as it does at runtime. Both spread `autoImportOptions` and `componentAutoImportOptions` from `frontend-auto-import.config.mjs`; do not inline either option set. Only stub children that need a live runtime dependency, such as `Link`.
- Component auto-registration only rewrites compiled SFC templates. A runtime `template` string in a spec still needs an explicit component reference.
- Auto-imported symbols used only inside a `<template>` are not typed by `vue-tsc`, because the generated `declare module 'vue'` augmentation does not merge into `@vue/runtime-core`. Derive a `computed` in `<script setup>` rather than calling an auto-imported helper directly in markup.
- Module Vue components are namespace-registered; use tags like `<UsersTable />` and `<UsersDeleteUserDialog />` instead of manual imports.
- `Link` and `Head` are resolver-provided; do not manually import them.
- The ESLint config currently enforces:
    - no direct imports of auto-imported composables/stores/libs/utils/helpers/components
    - no cross-module imports between feature modules
    - no inline `fields = [...]` form arrays in pages/components
    - no duplicated navigation arrays in pages/layouts/composables
    - no `fetch(...)` calls inside feature page files
    - no `as unknown as Record<string, unknown>`
    - no explicit any type (`@typescript-eslint/no-explicit-any`)
- Restricted imports use `@typescript-eslint/no-restricted-imports` with `allowTypeImports: true`. Auto-import only provides runtime values, so `import type { ... }` from a restricted path stays legal everywhere.
- Three scopes are deliberately exempt from the auto-import restriction:
    - files inside the auto-imported directories themselves (`autoImportSourceGlobs`), which wire up their own siblings with explicit imports instead of relying on auto-import resolving back into the directory being scanned
    - test files, which must be able to name real components and module namespaces for mounting and spying
    - UI primitives, which retain explicit imports while receiving all other lint and type checks
- Feature-module source and test files still enforce the cross-module boundary rule, including type-only imports.

### Frontend Ownership Rules

- Module-specific frontend code must stay under `resources/js/modules/<module>/**`.
- Feature modules must not import other feature modules directly.
- If code is needed across modules, promote it to a shared layer instead of crossing module boundaries.
- `resources/js/types/index.d.ts` is for app-shell/shared UI types, not duplicated backend domain contracts.

### Navigation And Breadcrumb Contracts

- Shared navigation config: `resources/js/config/navigation.ts`
- Shared breadcrumb builders: `resources/js/config/breadcrumbs.ts`
- Active-state resolution lives in `resources/js/composables/useNavigation.ts`
- Dashboard CRUD navigation discovery lives in `resources/js/config/dashboard-crud-navigation.ts`
- Generated CRUD dashboard navigation contracts live at `resources/js/modules/<module>/contracts/dashboard-nav.ts`
- Do not duplicate navigation or breadcrumb arrays inline in pages/components/layouts.

## Current Route Contract

### Web Routes

- Marketing:
    - `GET /` -> `marketing.home`
- Auth guest routes under `/auth`:
    - `GET /auth/register` -> `auth.register.create`
    - `POST /auth/register` -> `auth.register.store`
        - middleware: `guest`, `throttle:auth-sensitive`
    - `GET /auth/login` -> `auth.login.create`
    - `POST /auth/login` -> `auth.login.store`
    - `GET /auth/forgot-password` -> `auth.password.request`
    - `POST /auth/forgot-password` -> `auth.password.email`
        - middleware: `guest`, `throttle:auth-sensitive`
    - `GET /auth/reset-password/{token}` -> `auth.password.reset`
    - `POST /auth/reset-password` -> `auth.password.store`
        - middleware: `guest`, `throttle:auth-sensitive`
- Authenticated auth routes under `/auth`:
    - `GET /auth/verify-email` -> `auth.verification.notice`
    - `GET /auth/verify-email/{id}/{hash}` -> `auth.verification.verify`
        - middleware: `auth`, `signed`, `throttle:6,1`
    - `POST /auth/email/verification-notification` -> `auth.verification.send`
        - middleware: `auth`, `throttle:auth-sensitive`
    - `GET /auth/confirm-password` -> `auth.password.confirm`
    - `POST /auth/confirm-password` -> `auth.password.confirm.store`
    - `POST /auth/logout` -> `auth.logout`
- App shell:
    - `GET /app/dashboard` -> `app.dashboard`
        - middleware: `auth`, `verified`
- Settings routes under `/app/settings`:
    - `GET /app/settings/profile` -> `app.settings.profile.edit`
    - `PATCH /app/settings/profile` -> `app.settings.profile.update`
    - `DELETE /app/settings/profile` -> `app.settings.profile.destroy`
    - `GET /app/settings/password` -> `app.settings.password.edit`
    - `PUT /app/settings/password` -> `app.settings.password.update`
    - `GET /app/settings/appearance` -> `app.settings.appearance`
    - `GET /app/settings` redirects to `/app/settings/profile`
    - middleware: `auth`
- Admin users routes under `/app/admin/users`:
    - `GET /app/admin/users` -> `app.admin.users.index`
    - `POST /app/admin/users` -> `app.admin.users.store`
    - `PUT /app/admin/users/{user}` -> `app.admin.users.update`
    - `PATCH /app/admin/users/{user}` -> `app.admin.users.update`
    - `DELETE /app/admin/users/{user}` -> `app.admin.users.destroy`
    - middleware: `auth`, `verified`, `can:manage-users`

### API Routes

- API broadcast auth endpoints defined in `routes/api.php`:
    - `GET|POST /api/broadcasting/auth`
    - `GET|POST /api/broadcasting/user-auth`
    - middleware: `auth:sanctum`
    - CSRF verification removed for these endpoints
- Versioned API under `/api/v1`
- Current user endpoint:
    - `GET /api/v1/me` -> `api.v1.me.show`
    - middleware: `auth:sanctum`
- Admin users API under `/api/v1/admin/users`:
    - `GET /api/v1/admin/users` -> `api.v1.admin.users.index`
    - `POST /api/v1/admin/users` -> `api.v1.admin.users.store`
    - `PUT /api/v1/admin/users/{user}` -> `api.v1.admin.users.update`
    - `DELETE /api/v1/admin/users/{user}` -> `api.v1.admin.users.destroy`
    - middleware: `auth:sanctum`, `can:manage-users`

### Gates And Policies

- `AuthServiceProvider` registers policies from `ModuleRegistry::policyMap(...)`.
- `AuthServiceProvider` requires all discovered module gate files.
- Current module gate:
    - `manage-users` in `app/Modules/Users/Routes/gates.php`
- Current module policy:
    - `app/Modules/Users/Policies/UserPolicy.php`

## Type-Safety Rules

### Backend

- Naming must stay explicit and predictable.
- Controllers should be transport-oriented and action-specific.
- Queries should expose read behavior.
- Commands should expose write behavior.
- Handlers should orchestrate shared use cases across transports when needed.
- DTOs crossing the backend/frontend boundary must remain globally unique and module-prefixed when generated CRUD naming applies.
- Use DTOs and enums instead of untyped arrays or bounded strings whenever data crosses layers.
- Any backend DTO or enum consumed by the frontend must be exported through the TypeScript transformer.
- Prefer Spatie Data classes for payload/query/page contracts.
- Annotate frontend-facing DTOs/enums with `#[TypeScript]`. Enums under `app/**` are collected regardless of the attribute.
- TypeScript generation is configured in `app/Providers/TypeScriptTransformerServiceProvider.php`. Transformer v3 has no config file; do not reintroduce `config/typescript-transformer.php`.
- That provider is the single place that owns the generated contract surface. Current settings:
    - `DataClassTransformer()` so nullable DTO properties remain `foo: T | null`, matching serialized responses; optional fields must use explicit backend `Optional`/`Lazy` semantics
    - `EnumTransformer(useUnionEnums: false)` so PHP enums become native TypeScript enums
    - `FlatModuleWriter('app-data.ts')` into `resources/js/types`, keeping one flat ES module
    - `withoutManifest()` so no transformer manifest file lands in `resources/js/types`
- Route and controller type generation from the transformer stays off. Wayfinder owns that surface.
- Current shared auth user contract is `App\Modules\Shared\Data\UserViewData|null`; do not serialize the raw user model into Inertia props.
- `SharedPageData` and its nested `SharedAuthData`, `SharedFlashData`, and `SharedQuoteData` own application-wide Inertia props. `AppPageProps` composes the generated type with page-specific props. Flash fields are always present as `string|null`; do not redeclare these shapes in TypeScript.
- Request DTO hydration must be the canonical transport boundary.
- Services, queries, commands, and handlers must accept DTOs or explicit typed parameters, never mixed arrays.
- Prefer module-prefixed DTO names for generated CRUD contracts:
    - `BillingIndexStoreData`
    - `BillingIndexListItemData`
    - `BillingIndexPageData`
- Use enums for constrained sort/order/event/channel values.
- Current server-table sort direction enum is `App\Modules\Shared\Enums\SortDirection`.
- Current users sort enum is `App\Modules\Users\Enums\UserSortBy`.

### Frontend

- Use `<script setup lang="ts">`.
- Keep strict TypeScript assumptions intact.
- Consume backend-generated contracts from `resources/js/types/app-data.ts`.
- Do not recreate backend-owned DTOs, enums, realtime payloads, or route payload shapes manually.
- Prefer `FormValuesFromData<...>` from `resources/js/lib/forms.ts` when form values come from backend DTOs.
- Prefer `defineFormContract(...)` and `defineFormFields(...)` for all form schemas. Both live in `resources/js/lib/forms.ts` and are auto-imported; do not import them manually. `resources/js/types/base-ui.ts` keeps the field/schema _types_ only.
- Prefer `useSchemaResourceForm(...)` over hand-wired form state in page/components when the form matches the shared resource pattern.
- Use generated route/action helpers rather than hardcoded URLs.
- Use `useApiQuery`, `useApiMutation`, and `apiRequest` for API-driven state.
- `useApiQuery` is client-only by design: it never fetches or touches its shared cache during SSR. Server-rendered page data must arrive through Inertia props.
- `useApiQuery` semantics worth knowing before changing it:
    - Concurrent consumers of the same cache key share one request, including a forced `refresh()`. Only the raw fetch is shared; `select` and `mapError` stay per caller.
    - `queryCache` stores the raw `queryFn` result, never a `select` projection, so one entry can serve consumers that project the same key differently. `getApiQueryCacheData`/`setApiQueryCacheData` operate on that raw shape.
    - Invalidations and explicit cache writes version the key and drop its in-flight entry, so a request that started earlier cannot be joined or overwrite newer/optimistic data when it settles.
    - A request for an earlier reactive key may populate that key's cache, but it cannot overwrite the composable state for the current key.
    - String and array keys have distinct serialized identities. Delayed retries stop after a key change, invalidation, or identity-driven cache clear so they cannot read new inputs under an old key.
    - A projected result type requires an explicit `select`; identity queries preserve `TData` and cannot assert an unrelated result type.
    - A disabled query is never `isLoading`; disabling it supersedes observer updates from in-flight work, and `isSuccess` additionally requires resolved data.
- The app root clears the query cache synchronously when authenticated identity changes. Keep this lifecycle in `resources/js/components/AppRoot.vue`; account data must not survive logout or account switching. Full clears synchronously reset mounted query/mutation data and errors. Mutations check that context before sending a write and between asynchronous callbacks; stale contexts reject with `stale_auth_context` and skip subsequent callbacks and invalidations. Already running callbacks and server writes cannot be cancelled by a cache reset.
- Query/mutation error types incompatible with `ApiError` require `mapError`. Mutation pending state covers all concurrent requests; the latest invocation owns displayed data/errors. Reset clears displayed state without pretending outstanding work has stopped.
- Mutation callback failures propagate without reclassifying successful writes as failures. Cache invalidation precedes success callbacks, and settlement runs once even when a success or error callback throws.
- Wayfinder's generated route/action helpers are the only route surface. Ziggy is deliberately not a dependency: its `route()` is string-keyed rather than type-checked, and shipping its route table in every Inertia response duplicates what Wayfinder already generates at build time.
- `apiRequest` callers must validate payloads with `parseResponse` when a typed runtime contract matters.
- `apiRequest` is the canonical place for `X-Socket-ID` propagation.
- Same-origin mutations read the current `XSRF-TOKEN` cookie into `X-XSRF-TOKEN`; no static CSRF meta tag is needed. Automatic CSRF and socket headers must not be sent to another origin. Existing URL queries and fragments must survive added query options.
- Realtime channel strings must be derived from backend-owned patterns through `resolveRealtimeChannel(...)`.
- Shared UI primitives must include baseline accessibility: visible focus states, meaningful `aria-*` labels for icon-only controls, keyboard-operable interactions, and color-contrast-safe active/focus states.
- Schema form checkboxes own their inline label; `BaseFieldShell` hides its duplicate label for those fields. Select triggers receive the field ID so the shell label targets the focusable control.
- Schema controls associate descriptions and errors through `aria-describedby` and expose invalid/required state. Processing disables the form's fields. Read-only text remains selectable; choice/file controls disable interaction. Every choice variant honors disabled options.
- Schema form IDs use a per-instance Vue `useId()` prefix so repeated field names retain unique, SSR-stable label and feedback targets. Processing also prevents another submit event.
- Avoid unsafe casts like `as User`; guard nullable values explicitly.

### Backend-Driven Contract Pipeline

1. Define or update the backend enum / DTO.
2. Add `#[TypeScript]` if the frontend consumes it.
3. Use it in requests, queries, commands, handlers, controllers, resources, and realtime payloads.
4. Run generation commands.
5. Consume the generated contract from `@/types/app-data`.
6. Update tests.

## Realtime Standard

### Backend

- Reverb is the current default broadcaster in `.env.example`.
- Shared notification channel authorization is defined in `app/Modules/Shared/Routes/channels.php`.
- Users realtime channel authorization is defined in `app/Modules/Users/Routes/channels.php`.
- Root channel aggregation lives in `routes/channels.php`.
- Current backend-owned realtime enums:
    - `App\Modules\Shared\Enums\SharedRealtimeChannel`
    - `App\Modules\Users\Enums\UsersRealtimeChannel`
    - `App\Modules\Users\Enums\UsersRealtimeEvent`
    - `App\Modules\Users\Enums\UsersRealtimeAction`
- Presence member payload contract:
    - `App\Modules\Shared\Realtime/Data/PresenceMemberData`
- Broadcast notification payload contract:
    - `App\Modules\Users/Data/UserManagementNotificationData`
- Keep domain events separate from broadcast events.
- Channel registration, broadcast destinations, notification routing, and frontend subscriptions must all consume the canonical channel enums. Resolve parameterized destinations through `ChannelPatternResolver`; do not repeat channel string literals outside their owning enums.
- Let listeners translate domain events into realtime broadcasts and notifications.

### Frontend

- Initialize Echo only through `configureRealtime()` in `resources/js/lib/realtime/config.ts`, which `resources/js/app.ts` calls once for both the client and SSR.
- `configureRealtime()` is SSR-aware: under `import.meta.env.SSR` it configures the `null` broadcaster, so realtime composables resolve to inert channels instead of throwing or opening a WebSocket while the server renders. Never make a realtime composable depend on a live connection at `setup()` time.
- Shared realtime frontend helpers live in:
    - `resources/js/lib/realtime/config.ts`
    - `resources/js/lib/realtime/channels.ts`
    - `resources/js/composables/useRealtime.ts`
    - `resources/js/composables/useRealtimeConnection.ts`
- Current shared realtime composables:
    - `useRealtimeEvent`
    - `useRealtimeModel`
    - `useRealtimeNotification`
    - `useRealtimePresence`
    - `useRealtimeConnection`
- Feature-level usage helpers should live in module-local `contracts/realtime.ts`.
- The current reference implementation is `resources/js/modules/users/contracts/realtime.ts`.

### Runtime

- Queue workers must prioritize `realtime,high,default`.
- Local dev and production-style runtime must run Reverb alongside queue workers.
- Frontend realtime config currently supports:
    - session auth via `/broadcasting/auth` and `/broadcasting/user-auth`
    - bearer auth via `/api/broadcasting/auth` and `/api/broadcasting/user-auth`
- Do not hand-attach broadcast auth endpoints in feature code.

## Forms, Tables, And Page Data

- Form schemas belong in `resources/js/modules/**/forms/*-form-schema.ts`.
- Do not define inline schema arrays in pages/components.
- Current shared form contracts live in:
    - `resources/js/lib/forms.ts`
    - `resources/js/types/base-ui.ts`
    - `resources/js/components/base/forms/**`
- Server-driven listing pages must use the shared table stack:
    - `resources/js/composables/useServerDataTable.ts`
    - `resources/js/components/base/table/**`
- Initial query state must be derived via `resolveServerTableInitialQuery(...)`.
- That derivation reads the request URL from the shared `location` prop, which `HandleInertiaRequests::share()` sets from `$request->fullUrl()`. It must stay absolute and keep the query string: Inertia's own `page.url` is relative, and `$request->url()` would drop the query and silently reset a shared or bookmarked listing URL to the default sort.
- Current standard server-table query contract:
    - `page: number`
    - `perPage: number`
    - `search?: string`
    - `sortBy?: string`
    - `sortDirection?: 'asc' | 'desc'`
- Current allowed users `sortBy` values:
    - `name`
    - `email`
    - `role`
    - `created_at`

## Notifications Standard

- Global toasts are handled via `useToast` + `AppToaster`.
- Inertia flash props (`message`, `error`, `status`) are bridged to toasts through `useFlashToasts`.
- Toasts are client-only: `useToast` drops them during SSR and `AppToaster` renders none until it is mounted, which keeps the server output and the first client render identical. Do not remove either guard without moving the toast state out of module scope.
- Use inline messages only for persistent instructional content that should not be transient.

## Error And Exception Contract

- Web flows should prefer standard Laravel validation, redirects, flash messaging, and shared Inertia error handling.
- API flows should prefer `ApiResponder` plus consistent JSON/resource responses rather than ad hoc payload shapes.
- Do not introduce feature-local exception handling conventions when the shared application exception flow already covers the case.
- Throw exceptions deliberately and only when they represent a real exceptional path that callers can handle consistently.

## Security Rules

- Enforce authorization through policies, gates, and route middleware.
- Sensitive auth endpoints must use the `auth-sensitive` rate limiter.
- Password confirmation uses this limiter in addition to registration, reset, and verification notification endpoints.
- The `User` model invalidates email verification whenever its email changes through a model update, covering profile and admin transports. Bulk SQL updates bypass model events and must preserve this invariant explicitly.
- `AppServiceProvider` currently defines `auth-sensitive` as `5` requests per minute per IP.
- Security headers must continue to be set by `App\Http\Middleware\SecurityHeaders`.
- Current headers include:
    - CSP
    - `Referrer-Policy`
    - `X-Content-Type-Options`
    - `X-Frame-Options`
    - `Permissions-Policy`
- Non-production CSP is intentionally looser for local development.
- Audit and mutation metadata must preserve useful context while avoiding sensitive-field leakage.

## Database And Migration Rules

- Migrations must be reversible whenever reasonably possible.
- Avoid destructive schema changes without a safe transition plan.
- New searchable or sortable fields must be evaluated for indexing.
- Query changes must consider pagination, sorting, filtering, and N+1 behavior.
- Backfills or data repair logic must be explicit; do not hide data migrations inside unrelated application code.
- Model changes must preserve existing casts, auth behavior, and serialization contracts unless the task intentionally changes them.

## Generated Files

Do not hand-edit generated artifacts:

- `bootstrap/cache/modules.php`
- `resources/js/routes/**`
- `resources/js/actions/**`
- `resources/js/wayfinder/index.ts`
- `resources/js/types/app-data.ts`
- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

Generated artifacts are canonical outputs of backend contracts and generator workflows.
Do not shadow them with manual duplicates.

## Generation Commands

- Route/type/helper generation:
    ```bash
    composer generate
    ```
- `composer generate` currently runs:
    - `php artisan modules:cache --no-interaction`
    - `php artisan typescript:transform`
    - `php artisan wayfinder:generate`
- Full mutating cleanup:
    ```bash
    composer generate-and-cleanup
    ```
- `composer generate-and-cleanup` currently runs:
    - route/type/helper generation
    - `composer cleanup`
- `composer cleanup` runs two pipelines concurrently and groups their output:
    - `composer cleanup:php`
    - `npm run cleanup`
- `composer cleanup:php` currently runs, in order:
    - `vendor/bin/rector process`
    - `vendor/bin/pint`
    - `vendor/bin/phpstan analyse --ansi`
- PHP quality tools have canonical Composer entry points:
    - `composer refactor` / `composer refactor:check`
    - `composer format` / `composer format:check`
    - `composer analyse`
- Rector runs before Pint so AST rewrites always receive the final canonical formatting pass.
- `npm run cleanup` currently runs:
    - `npm run format`
    - `npm run lint`
    - `npm run typecheck`
- Non-mutating QA:
    ```bash
    composer qa:check
    ```
- `composer qa:check` runs `composer qa:php`, `npm run format:check`, `npm run lint:check`, and `npm run typecheck` concurrently. It must never rewrite source files.
- `composer qa:php` runs Rector in dry-run mode, Pint in test mode, then PHPStan.

### Script Performance Rules

- The PHP pipelines stay ordered because Rector, Pint, and PHPStan share the same files; only independent pipelines are parallelized.
- Aggregate scripts orchestrate through `concurrently` and reference the canonical single-tool scripts. Never inline a tool invocation that already has an entry point.
- Every tool that supports a result cache must keep one: Rector and PHPStan under `storage/framework/cache/**`, Prettier, ESLint, and `vue-tsc` under `node_modules/.cache/**`. Frontend caches use the content strategy so they survive branch switches and CI checkouts.
- Pint runs single-process. `--parallel` spawns a worker per core and is slower than a single process at this repository's file count; only revisit it if the PHP file count grows by an order of magnitude.

## Dependency Addition Policy

- Prefer the existing Laravel, Vue, Inertia, Spatie Data, and shared project abstractions before adding new dependencies.
- New packages require strong justification and must not overlap meaningfully with tools already in the stack.
- Prefer native framework capabilities when they are already sufficient and project-consistent.
- Avoid introducing dependencies that make generated contracts, SSR, strict typing, or CI harder to maintain.

## Module Generator Contract

- Command:
    ```bash
    php artisan generate:module <ModuleName>
    ```
- Supported scaffolds:
    - `page` - Frontend-only page contracts
    - `crud` - Full backend + frontend CRUD
    - `api` - Backend API only
    - `crud-api` - Full CRUD with both web and API
- `--extend` requires the module to already exist.
- Fresh mode fails if the module already exists.
- Interactive shells prompt for scaffold/profile choices and, unless disabled, per-file generation confirmation.
- Use `--no-file-prompts` to skip per-file confirmations.
- Use `--dry-run` to inspect the plan without writing files.
- Use `--force` to overwrite existing generated files.
- Use `--base-path` only for tests or isolated generation scenarios.
- Command options contract:
    - `--route-profile=app|public|custom` (non-interactive defaults to `app`)
    - `--roles=all|admin,user` (required when scaffolding includes app CRUD web routes or protected API routes; supports `all` or comma-separated values from `App\Enums\UserRole`)
    - `--route-prefix=...` and `--route-name-prefix=...`
    - `--middleware=auth,verified`
    - `--api-route-profile=protected|public|custom` (non-interactive defaults to `protected`)
    - `--api-route-prefix=...` and `--api-route-name-prefix=...`
    - `--api-middleware=auth:sanctum`
    - `--no-api-resource`, `--no-api-test`, `--no-model`, `--no-page`

### Current Generator Behavior

- Template rendering rejects missing tokens before writes; module and page identifiers must start with a letter after normalization.
- Rendered PHP stubs are syntax-checked and reject declaration/import name collisions before writes. Keep top-level class imports on individual lines so validation and import sorting share the same convention.
- Frontend scaffolds use `AppLayout` only when their configured middleware contains `auth` or `auth:*`; otherwise they use `MarketingPageLayout`. The `page` scaffold honors explicit route-profile and middleware options for layout selection without generating backend routes.
- CRUD/API model names also reject PHP reserved names before files are written.
- Generated listings use `PaginationQueryRequest` / `PaginationQueryData`, bounded to 100 rows per page, with stable ordering. CRUD page DTOs include shared `PaginationData` metadata and the page uses the shared pagination component.
- Every paginated page DTO exposes the same envelope: `items` plus a shared `PaginationData`. `UsersIndexPageData` follows it too, so the reference module and generator output have one shape. Do not redeclare paginator metadata per module.
- `TemplateRenderer` sorts the rendered `use` block through `PhpUseStatementSorter`. A stub cannot know whether `App\Modules\Shared\...` sorts before or after the module namespace, so imports are ordered after rendering instead. Stubs stay Rector- and Pint-clean by construction: name parameters after their type, and keep generated arrays free of trailing commas.
- Resource API pagination links preserve query parameters. Plain API metadata uses `PaginationData` too. Server tables pass a reactive `initialQuery` to `useServerDataTable` so preserved-state redirects synchronize pagination, search, and sort without another visit. Users derives it from the current location and pagination props; generated pages derive it from pagination props.
- A `page` scaffold has frontend-only form values until connected to a backend endpoint. When connecting it, derive form values from that endpoint's generated DTO. It must never import a DTO that the scaffold did not generate.
- `GeneratedPaginationTest` executes all four API variants (standalone/combined, resource/plain) and checks later pages, invalid input, and CRUD page metadata.

- `page` scaffolds frontend-only page contracts:
    - `resources/js/modules/<module>/forms/<page-kebab>-form-schema.ts`
    - `resources/js/modules/<module>/pages/<Page>.vue`
    - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`
- `crud` scaffolds:
    - module-local requests, DTOs, queries, commands
    - web controller
    - web routes
    - page/list DTOs
    - resource manifest
    - optional gate file depending on role scope
    - model + migration unless skipped
    - frontend CRUD page/contracts/components unless `--no-page`
    - feature test
- `api` scaffolds:
    - module-local requests, DTOs, queries, commands
    - API controller
    - API routes
    - optional `JsonResource`
    - model + migration unless skipped
    - API feature test unless skipped
- `crud-api` scaffolds both web and API layers and additionally generates module-local handlers.

### Current Generated CRUD Frontend Files

- `resources/js/modules/<module>/contracts/<page-kebab>-crud.ts`
- `resources/js/modules/<module>/contracts/dashboard-nav.ts`
- `resources/js/modules/<module>/forms/<page-kebab>-form-schema.ts`
- `resources/js/modules/<module>/components/Table.vue`
- `resources/js/modules/<module>/components/<Page>FormDialog.vue`
- `resources/js/modules/<module>/components/<Page>DeleteDialog.vue`
- `resources/js/modules/<module>/components/<Page>DetailsDialog.vue`
- `resources/js/modules/<module>/pages/<Page>.vue`
- `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`

### Current Generated CRUD Backend Files

- `app/Modules/<Module>/Http/Controllers/<Page>Controller.php`
- `app/Modules/<Module>/Http/Requests/<Page>StoreRequest.php`
- `app/Modules/<Module>/Http/Requests/<Page>UpdateRequest.php`
- `app/Modules/<Module>/Data/<Module><Page>StoreData.php`
- `app/Modules/<Module>/Data/<Module><Page>ListItemData.php`
- `app/Modules/<Module>/Data/<Module><Page>PageData.php`
- `app/Modules/<Module>/Queries/<Model>Queries.php`
- `app/Modules/<Module>/Commands/<Model>Commands.php`
- `app/Modules/<Module>/Manifests/<Page>Resource.php`
- `app/Modules/<Module>/Routes/web.php`
- `app/Modules/<Module>/Routes/gates.php` when generated app CRUD or protected API routes are role-restricted
- `tests/Feature/<Module>/<Page>PageTest.php`
- `app/Models/<Model>.php` unless skipped
- `database/migrations/*_create_<table>_table.php` unless skipped

### Current Generated API Backend Files

- `app/Modules/<Module>/Http/Controllers/<Page>ApiController.php`
- `app/Modules/<Module>/Routes/api.php`
- `app/Modules/<Module>/Http/Resources/<Page>Resource.php` unless `--no-api-resource`
- `tests/Feature/<Module>/<Page>ApiTest.php` unless `--no-api-test`

### Current Generator Route Defaults

- Web `app` profile:
    - default prefix: `app/<module-kebab>`
    - default name prefix: `app.<module-kebab>`
    - default middleware: `auth`, `verified`
- Web `app` profile with admin-only role scope:
    - default prefix: `app/admin/<module-kebab>`
    - default name prefix: `app.admin.<module-kebab>`
    - default middleware: `auth`, `verified`, `can:manage-<module-kebab>`
- Web `public` profile:
    - default prefix: `<module-kebab>`
    - default name prefix: `<module-kebab>`
    - default middleware: none
- API `protected` profile:
    - default module route prefix: `v1/admin/<module-kebab>` (Laravel applies the external `/api` prefix)
    - default name prefix: `api.v1.admin.<module-kebab>`
    - default middleware: `auth:sanctum`, plus `can:manage-<module-kebab>` for role-restricted scopes
- API `public` profile:
    - default module route prefix: `v1/<module-kebab>` (Laravel applies the external `/api` prefix)
    - default name prefix: `api.v1.<module-kebab>`
    - default middleware: none

### Resource Manifest Rules

- Generated CRUD resources own a manifest at `app/Modules/<Module>/Manifests/<Page>Resource.php`.
- The manifest is the module-local source of truth for:
    - route profile
    - route prefix
    - route name prefix
    - role scope
    - middleware
    - API defaults
    - table columns
    - mobile fields
    - form fields
    - realtime enablement
- When regenerating an existing CRUD resource, the generator should reuse manifest defaults instead of forcing the same options to be passed again.

## Performance Guardrails

- Avoid repeated filesystem discovery, reflection, or route scanning outside the existing registry/generation flow.
- Avoid N+1 queries and unnecessary model hydration on backend listing/detail endpoints.
- Prefer eager loading and focused selects when query complexity grows.
- Avoid unnecessary frontend reloads, refetches, or watchers when existing state can be updated deterministically.
- Reuse shared caches, query utilities, and realtime invalidation hooks instead of duplicating fetch logic.
- Performance optimizations must remain readable and consistent with the codebase; do not introduce obscure micro-optimizations.

## Testing Standards

- Pest 5 is the canonical backend test runner. Write backend tests in native Pest functional syntax.
- `tests/Pest.php` binds both `Feature` and `Unit` suites to `Tests\TestCase`.
- Pest's compact printer is configured centrally in `tests/Pest.php`; do not duplicate `--compact` across scripts.
- `composer test` runs the suite in parallel. `composer test:serial` is the single-process escape hatch for debugging output, and `composer test:fast` adds test impact analysis when `pcov` or Xdebug is installed.
- Each test script is a single command, so extra arguments reach Pest directly: `composer test -- --filter=Registration`.
- `phpunit.xml` redirects `APP_CONFIG_CACHE`, `APP_EVENTS_CACHE`, and `APP_ROUTES_CACHE` to paths that never exist, so a locally cached boot manifest can never override the declared test environment. Do not reintroduce a cache-clearing step in the test scripts.
- PHPUnit is configured to fail on every reported issue and to detect unexpected global-state changes or test output.
- PHPStan analyzes native Pest closures through `pestphp/pest-plugin-phpstan`; keep its extension registered in `phpstan.neon.dist`.
- PHPStan runs at level 9 with `phpstan/phpstan-strict-rules` and `phpstan/phpstan-deprecation-rules`, with no baseline or ignored-error escape hatch.
- The strict rule forbidding dynamic calls to static methods is disabled because Pest/PHPUnit assertions and Laravel fluent builders intentionally expose instance syntax over methods declared static. Keep all other strict rules enabled.
- Apply `RefreshDatabase` only in files that need database isolation; do not make it global.
- Reusable backend test setup and fixture behavior belongs under `tests/Support/**`, not in duplicated file-local functions.
- Module generator backend test stubs must generate native Pest tests.
- Every behavior change must include tests for:
    - happy path
    - failure path
    - relevant edge cases
- Extend the nearest existing test suite instead of creating disconnected coverage patterns.
- Current backend test areas already include:
    - auth flows
    - settings flows
    - dashboard and marketing rendering
    - users web management
    - users realtime
    - API v1 endpoints
    - security headers
    - module registry / route / gate / channel / listener / provider discovery
    - generator behavior
- Frontend logic tests live as `resources/js/**/*.test.ts` and run under Vitest with `jsdom`.

## Test Placement Rules

- Backend transport behavior belongs in `tests/Feature/**`.
- Backend pure/domain orchestration behavior belongs in `tests/Unit/**`.
- Module-specific backend tests should stay grouped under their module namespace when practical.
- Frontend composable, contract, and utility tests should live close to the code under `resources/js/**/__tests__/**` or `resources/js/**/*.test.ts`.
- Generator and infrastructure behavior should be covered near their owning area, not mixed into unrelated feature suites.

## Docs Sync Rules

- When behavior, architecture, setup, or generation workflows change, update the relevant docs in the same task.
- Review and update these files when relevant:
    - `AGENTS.md`
    - `README.md`
    - `docs/frontend-automation.md`
    - `docs/how-to-add-module-page.md`
    - `docs/deployment.md`
- Do not leave known stale instructions behind after changing the implementation.

## Quality Gate

Always run after changes:

```bash
composer generate-and-cleanup
composer test
```

For frontend behavior or composable changes, also run:

```bash
npm run test:unit
```

For non-mutating static verification, run:

```bash
composer qa:check
```

If backend route, enum, DTO, channel, provider, gate, listener, or module-registry contracts changed, `composer generate` is mandatory before considering the task complete.

`composer generate-and-cleanup` must finish without unresolved errors or warnings before a task is considered complete.

## CI Compatibility

Local changes must remain compatible with the existing CI expectations:

The test workflow checks generated types, auto-import/component declarations, routes, and actions after the client/SSR build. Both modified tracked files and untracked generated files fail the gate; regenerate and commit the complete contract surface together.

The shared setup uses `actions/cache@v6` with the Node 24 action runtime. Dependency and patch locks are part of the cache identity.

The test workflow audits both locked dependency graphs. Both mutating and non-mutating ESLint commands fail on warnings.

A second CI job runs `composer qa:generated` (`scripts/verify-generated-module.sh`). It snapshots tracked changes and untracked source into an isolated checkout, installs both locked dependency graphs, scaffolds protected and public modules, builds client/SSR assets, and runs `composer generate-and-cleanup`, `composer qa:check`, both suites, and whitespace verification including new files. A generated public-page SSR regression uses the real layout and a guest Inertia page context. The source checkout and index remain untouched even on failure; only the temporary checkout is deleted. Run it after touching anything under `stubs/module-generation` or the scaffold planner. Name-dependent formatting is handled by the canonical cleanup inside the gate.

- Pint
- Rector dry-run
- PHPStan level 9 with strict and deprecation rules
- Prettier
- ESLint
- TypeScript typecheck
- Pest / `composer test`
- Vitest for frontend logic changes

## Laravel Boost MCP Workflow

When Laravel Boost MCP tools are available to the agent:

- Use `search-docs` before Laravel/Inertia/Wayfinder/Sanctum/Tailwind ecosystem changes.
- Use `list-artisan-commands` before running Artisan commands.
- Run Artisan with `--no-interaction` when the specific command supports it.
- Use MCP equivalents (`tinker`, `database-query`, `browser-logs`) when applicable. If MCP tools fail, fallback to equivalent terminal shell commands.

## Breaking Change Policy

- Treat the following as breaking unless every consumer is updated in the same task:
    - route names or URI contracts
    - DTO or resource payload shapes
    - enum values
    - generated type/action/route outputs
    - realtime channel patterns, event names, or payload contracts
    - policy/gate ability names
- If a breaking contract change is intentional, update all affected backend code, frontend consumers, tests, generated artifacts, and docs together.

## Final Enforcement Rules

- Do not hand-wave or defer required implementation steps.
- Do not preserve stale abstractions because they already exist in a draft, comment, or doc.
- Do not add new abstractions when an existing query, command, handler, responder, composable, or base component already solves the problem.
- Do not move module-specific code into shared layers prematurely.
- Do not couple frontend modules directly to each other.
- Do not bypass generated contracts with manual copies.
- Do not ship code that is untested, partially wired, or inconsistent with the rest of the repository.
- The correct solution is the one that is accurate, complete, typed, cohesive, maintainable, and aligned with the current implementation of this codebase.
- Never lose full context of things you started working on (tasks, full logic of each task, tasks done, tasks remaining) until everything is successfully done.
- Never make assumptions; if you don't know something or have some doubts, stop and ask questions.
