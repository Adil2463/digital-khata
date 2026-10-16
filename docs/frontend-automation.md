# Frontend Automation

## TypeScript compiler and editor

`npm run typecheck` runs `vue-tsc --noEmit` with the TypeScript 7.0.2 native checker through
[TypeScript Native Bridge](https://github.com/johnsoncodehk/typescript-native-bridge).
The `typescript` dependency is an exact npm alias to `6.0.3-bridge.18.tsgo.7.0.2`, and
`overrides.typescript = "$typescript"` keeps all compiler consumers on the same implementation.
The `6.0.3` prefix describes the classic JavaScript API facade; `tsgo.7.0.2` describes the checker.
The bridge prints `TNB ACTIVE` when its checker starts; this is an informational message.

This third-party bridge preserves the API used by Vue's virtual files, ESLint, and Prettier's
import organizer. [Microsoft's stock TypeScript 7 package](https://devblogs.microsoft.com/typescript/announcing-typescript-7-0/)
does not provide that API. Replace the bridge with stock TypeScript only when all three consumers
support it, then remove the npm override and revalidate the complete pipeline. Keep bridge upgrades
exactly pinned and commit the regenerated lockfile. Do not use `--force` or `--legacy-peer-deps`.

Run `npm ci` with optional dependencies enabled. The bridge ships native binaries for Windows,
macOS, and glibc Linux 2.31+. The repository's Ubuntu CI and Sail images meet that requirement.
Alpine/musl is unsupported; use a glibc frontend build stage if deploying to Alpine.

In VS Code, enable Vue - Official and choose **TypeScript: Select TypeScript Version → Use Workspace Version**.
The committed settings point the SDK at `node_modules/typescript/lib`. Editors using the older settings
names can set `typescript.tsdk` to that same path and `typescript.enablePromptUseWorkspaceTsdk` to `true`.
Use the classic language service with Vue's plugin; disable the separate TypeScript 7 language server
if it takes over the workspace, since that server does not support the Vue plugin model.

`resources/js/types/__tests__/compiler.test.ts` invokes the real Vue checker against temporary fixtures.
It verifies TypeScript 7 Unicode inference and ensures invalid component props, template methods,
and nullable backend contracts still produce compiler errors. It runs with `npm run test:unit` in CI.

## Module and page scaffolding

Use the Artisan generator to scaffold a module with backend + frontend contracts:

```bash
php artisan generate:module <ModuleName> --scaffold=crud --page=<PageName>
```

Example:

```bash
php artisan generate:module Billing --scaffold=crud --page=Index
```

Available scaffold targets:

- `page`: frontend form/page/test contracts only
- `crud`: web CRUD scaffold (+ optional frontend page contracts)
- `api`: API CRUD scaffold
- `crud-api`: both web CRUD and API CRUD

For existing modules, use extend mode with page scaffold to add frontend contracts only:

```bash
php artisan generate:module Users --extend --scaffold=page --page=InviteUser
```

In interactive shells, the command first asks scaffold/profile questions, then asks for confirmation per generated file.
Use `--no-file-prompts` to skip per-file confirmations and generate all planned files directly.

Generated frontend files:

- `--scaffold=page`:
    - `resources/js/modules/<module>/forms/<page>-form-schema.ts`
    - `resources/js/modules/<module>/pages/<Page>.vue`
    - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`
- `--scaffold=crud` (when page generation is enabled):
    - `resources/js/modules/<module>/contracts/<page>-crud.ts`
    - `resources/js/modules/<module>/contracts/dashboard-nav.ts`
    - `resources/js/modules/<module>/forms/<page>-form-schema.ts`
    - `resources/js/modules/<module>/components/Table.vue`
    - `resources/js/modules/<module>/components/<Page>FormDialog.vue`
    - `resources/js/modules/<module>/components/<Page>DeleteDialog.vue`
    - `resources/js/modules/<module>/components/<Page>DetailsDialog.vue`
    - `resources/js/modules/<module>/pages/<Page>.vue`
    - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`

Generated backend additions (fresh module mode):

- `app/Modules/<Module>/Http/Controllers/<Page>Controller.php` (`crud`)
- `app/Modules/<Module>/Http/Controllers/<Page>ApiController.php` (`api`)
- `app/Modules/<Module>/Http/Requests/<Page>StoreRequest.php`
- `app/Modules/<Module>/Http/Requests/<Page>UpdateRequest.php`
- `app/Modules/<Module>/Data/<Module><Page>StoreData.php`
- `app/Modules/<Module>/Data/<Module><Page>ListItemData.php`
- `app/Modules/<Module>/Data/<Module><Page>PageData.php`
- `app/Modules/<Module>/Queries/<Model>Queries.php`
- `app/Modules/<Module>/Commands/<Model>Commands.php`
- `app/Modules/<Module>/Handlers/**` (`crud-api`)
- `app/Modules/<Module>/Manifests/<Page>Resource.php` (`crud`)
- `app/Modules/<Module>/Routes/web.php` (`crud`)
- `app/Modules/<Module>/Routes/api.php` (`api`)
- `app/Modules/<Module>/Routes/gates.php` (`crud`, role-restricted app routes)
- `app/Modules/<Module>/Http/Resources/<Page>Resource.php` (`api`, optional)
- `app/Models/<Model>.php`
- `database/migrations/*_create_<table>_table.php`
- `tests/Feature/<Module>/<Page>PageTest.php` (`crud`)
- `tests/Feature/<Module>/<Page>ApiTest.php` (`api`)

## Auto-import source of truth

`frontend-auto-import.config.mjs` is the canonical definition for auto-import symbols and directories.

Routing uses Inertia and generated Wayfinder helpers. The Vue Router preset is not enabled. Type-only symbols such as Inertia's `Method` use explicit `import type` declarations.

The following files consume it and must stay aligned:

- `vite.config.ts`
- `vitest.config.ts`
- `eslint.config.js`

It exports two contracts:

- **Symbol auto-import** (`autoImportDirs`, `autoImportImports`) - drives `unplugin-auto-import` in
  `vite.config.ts` and `vitest.config.ts`, and the restricted-import rules in `eslint.config.js`.
- **Component auto-registration** (`componentAutoImportOptions`, `inertiaComponentResolver`,
  `iconComponentPrefix`) - drives `unplugin-vue-components` in both `vite.config.ts` and
  `vitest.config.ts`, so a mounted component resolves `Ui*`, `Base*`, and module components in
  tests exactly as it does at runtime. Only stub children that need a live runtime dependency
  (for example `Link`, which needs the Inertia router).

### Generated declarations

Vite writes the following committed declaration files from this configuration:

- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

Do not edit these files manually. After changing auto-import configuration or component files,
run the complete generated-artifact check:

```bash
composer generate
npm run build:ssr
```

CI regenerates the same contracts as part of its shared setup step, then runs the quality checks
and the Vite client and SSR build against the regenerated artifacts.

Wayfinder 0.1.21 mixes platform line separators with LF Blade templates, which otherwise produces
different indentation and blank lines on Windows and Linux. The locked Composer patch in
`patches/wayfinder-portable-newlines.patch` normalizes generation to LF at the source. It applies
to all existing generation commands, including Vite's automatic regeneration. Keep generated
helpers excluded from manual formatting and keep the CI drift check strict.

`patches/wayfinder-query-method.patch` adds Laravel 13's `QUERY` verb to Wayfinder's generated
method union so query routes and redirects remain type-safe. Its regression covers route,
action, and form helpers. Remove it when upstream supports the verb and the regression passes.

`composer install` applies both patches to a fresh dependency installation and requires Git.
For an existing vendor installation, run `composer patches-repatch` after adopting the patch.
If the patch changes, run `composer patches-relock` followed by `composer patches-repatch`, then
regenerate and run the quality gates. Commit the patch, `patches.lock.json`, and regenerated
helpers together. Remove this patch when the upstream generator passes the portability tests
on both Windows and Linux.

### Import rules

ESLint combines `typescript-eslint`'s recommended configuration with `eslint-plugin-vue` and
`vue-eslint-parser` directly, including TypeScript expressions in templates and the `vue/block-lang`
rule. This preserves the existing rules without the unpatched `braces` dependency pulled in by
`@vue/eslint-config-typescript`. Vue typechecking remains a separate `vue-tsc` gate.

Vue typechecking and ESLint cover UI primitives as well as application components. Primitives keep explicit imports because the shadcn-vue CLI regenerates them.

Forward props to a reka-ui primitive with `useForwardedProps` / `useForwardedPropsEmits` from `@/lib/forward-props`, narrowing with `reactiveOmit` when a prop is consumed locally. reka-ui already omits undefined values while forwarding but types the result with every key present, which `exactOptionalPropertyTypes` rejects; the adapters restate that runtime contract in the type system and add no work of their own. Asserting `as Partial<...>` hides missing required props, and wrapping the forwarded object in another filter repeats what reka-ui just did. Reach for `omitUndefinedProps` only when the component builds the object itself, since the adapters read the calling component instance.

Server listing pages pass a reactive `initialQuery` getter or computed ref to `useServerDataTable`. Users derives the query from the current location and backend pagination; generated pages use pagination props. Preserved-state redirects synchronize pagination, search, and sort without another visit. Pending searches that now match server state do not issue a stale request.

The app root clears client query caches when authenticated identity changes. Clearing also synchronously resets mounted query and mutation data/errors; pending query completions cannot restore them. A query key change drops the previous key's data while the new result loads. After a full cache clear, enabled queries fetch again on an explicit refresh or a key/enabled change. SSR never fetches into or mutates these caches. Custom query/mutation error types require a mapper when they cannot represent `ApiError`, and mutation pending state accounts for overlapping requests.

String cache keys and array cache keys remain distinct, even when a string looks like serialized JSON. Delayed retries stop when their key changes or their cache revision is invalidated, including logout/account changes. Keep each key tied to one raw response shape; selectors may project that shape separately.

`resources/js/types/globals.d.ts` registers the backend-generated `SharedPageData` as Inertia's `sharedPageProps`, so `usePage()` and `$page` are typed without a type argument; `AppPageProps` composes the same type with page-specific props. Shared auth, quote, flash, appearance, and location types are not maintained manually. The same file declares every `VITE_*` variable the frontend reads and enables Vite's `strictImportMetaEnv`, so an undeclared `import.meta.env` key is a type error rather than `any`. Flash properties are present as `string | null`. Form controls expose labels, descriptions, validation errors, and required state to assistive technology. Form IDs include a per-instance `useId()` prefix, which remains stable through SSR hydration and unique when forms repeat field names. Processing disables fields and suppresses submission; read-only choice/file controls and disabled options cannot be changed.

Mutation callbacks run once per invocation. A successful write invalidates its cache keys before success callbacks run. Callback failures propagate to the caller without marking the write as failed or invoking rollback; settlement still runs if a success or error callback throws.

A full cache clear invalidates outstanding mutation contexts. They reject with `stale_auth_context` (or the custom mapped error), skip subsequent callbacks and invalidations belonging to the old account, and cannot start a write after asynchronous preparation completes under a changed account. The account is checked between asynchronous callbacks as well. A callback already running cannot be cancelled, and a write already sent may have persisted; this rejection does not imply server rollback. Pending state settles when the outstanding operation finishes.

Inertia's browser history is a separate cache of page props. `INERTIA_ENCRYPT_HISTORY` defaults to `true`, and successful login and logout clear its key on the next page response. Back navigation then requests protected data from the server instead of restoring an old account's encrypted snapshot. HTTPS or localhost is required; an explicit `false` environment override disables this behavior. See [Inertia history encryption](https://inertiajs.com/docs/v3/security/history-encryption).

`apiRequest` reads Laravel's current CSRF cookie for same-origin mutations, merges existing query parameters, and keeps URL fragments intact. Automatic CSRF and socket headers remain on the same origin.

`eslint.config.js` uses `@typescript-eslint/no-restricted-imports` with `allowTypeImports: true`:

- Runtime values from `@/composables/**`, `@/stores/**`, `@/lib/**`, `@/utils/**`,
  `@/modules/**/composables/**`, and `@/modules/**/helpers/**` must come from auto-import.
- `import type { ... }` from those paths stays legal, because auto-import only provides values.
- Files _inside_ those directories are exempt from auto-import restrictions: they wire up
  their own siblings with explicit imports instead of relying on auto-import resolving back
  into the directory being scanned. Feature-module boundaries still apply, including to
  module-local composables/helpers and tests.

Auto-imported symbols used only inside a `<template>` are not typed by `vue-tsc` (the generated
`declare module 'vue'` augmentation does not merge into `@vue/runtime-core`). Derive a
`computed` in `<script setup>` instead of calling an auto-imported helper directly in markup.
