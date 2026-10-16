#!/usr/bin/env bash

set -euo pipefail

# CI puts php on PATH; Herd on Windows only exposes a .bat shim, so allow both and an
# explicit override.
PHP="${PHP_BINARY:-}"
if [[ -z "${PHP}" ]]; then
    if command -v php >/dev/null 2>&1; then
        PHP=php
    elif command -v php.bat >/dev/null 2>&1; then
        PHP=php.bat
    else
        echo "Could not find php. Set PHP_BINARY to its path." >&2
        exit 1
    fi
fi

if command -v composer >/dev/null 2>&1; then
    COMPOSER=composer
else
    COMPOSER=composer.bat
fi

MODULE="${1:-Scaffoldgate}"
if [[ ! "${MODULE}" =~ ^[A-Z][a-z]+$ ]]; then
    echo "Module name must be a single capitalised word, got '${MODULE}'." >&2
    exit 1
fi

SOURCE="$(git rev-parse --show-toplevel)"
SOURCE_HEAD="$(git -C "${SOURCE}" rev-parse HEAD)"
SANDBOX="$(mktemp -d "${TMPDIR:-/tmp}/starter-kit-generated.XXXXXXXX")"
SANDBOX="$(cd "${SANDBOX}" && pwd -P)"

cleanup() {
    local status=$?
    cd "${SOURCE}"
    # Delete only this resolved temporary directory, never inferred source paths.
    rm -rf -- "${SANDBOX}"
    exit "${status}"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# Capture staged, unstaged, and untracked source without touching the source index,
# generated contracts, build outputs, credentials, or database.
git clone --quiet --no-hardlinks --no-checkout "${SOURCE}" "${SANDBOX}"
git -C "${SANDBOX}" checkout --quiet --detach "${SOURCE_HEAD}"
git -C "${SOURCE}" diff --binary HEAD | git -C "${SANDBOX}" apply --allow-empty
while IFS= read -r -d '' path; do
    mkdir -p "${SANDBOX}/$(dirname "${path}")"
    cp -P -- "${SOURCE}/${path}" "${SANDBOX}/${path}"
done < <(git -C "${SOURCE}" ls-files --others --exclude-standard -z)

cd "${SANDBOX}"
cp .env.example .env

echo "Installing locked dependencies in the isolated checkout"
"${COMPOSER}" install --no-interaction --no-progress --prefer-dist --optimize-autoloader
npm ci
"${PHP}" artisan key:generate --no-interaction

echo "--- Generating ${MODULE} ---"
"${PHP}" artisan generate:module "${MODULE}" \
    --scaffold=crud-api \
    --route-profile=app \
    --api-route-profile=protected \
    --roles=all \
    --no-file-prompts \
    --no-interaction

PUBLIC_MODULE="${MODULE}public"
"${PHP}" artisan generate:module "${PUBLIC_MODULE}" \
    --scaffold=crud-api \
    --route-profile=public \
    --api-route-profile=public \
    --no-file-prompts \
    --no-interaction
sed "s/__PUBLIC_MODULE__/${PUBLIC_MODULE,,}/g" scripts/generated-public-page.test.stub \
    > "resources/js/modules/${PUBLIC_MODULE,,}/pages/__tests__/public-render.test.ts"

# Build first to discover the new components before typechecking. The canonical
# cleanup also handles module-name-dependent formatting of generated markup.
"${COMPOSER}" generate
npm run build:ssr
"${COMPOSER}" generate-and-cleanup
"${COMPOSER}" qa:check
"${COMPOSER}" test
npm run test:unit
git add --intent-to-add -- .
git diff --check

echo "Generated-module gate passed; source checkout was left untouched."
