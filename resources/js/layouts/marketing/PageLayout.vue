<script setup lang="ts">
    const theme = appTheme
</script>

<template>
    <div class="relative min-h-svh overflow-hidden bg-background text-foreground">
        <div :class="['pointer-events-none absolute inset-x-0 top-0 h-[30rem]', theme.marketing.glowTop]" />
        <div :class="['pointer-events-none absolute inset-x-0 bottom-0 h-72', theme.marketing.glowBottom]" />

        <div class="relative flex min-h-svh flex-col">
            <MarketingHeader />

            <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-16">
                <slot />
            </main>

            <MarketingFooter />
        </div>
    </div>
</template>
