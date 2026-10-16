<script setup lang="ts">
    const theme = appTheme

    const appName = import.meta.env.VITE_APP_NAME || 'Laravel Starter Kit'
    const { marketingPrimaryAction, marketingSecondaryAction } = useNavigation()
</script>

<template>
    <Head title="Welcome" />

    <MarketingPageLayout>
        <section class="grid gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-center">
            <div class="space-y-6 sm:space-y-8">
                <span :class="theme.marketing.badge"> Premium Laravel + Vue starter kit </span>

                <div class="space-y-4">
                    <h1 class="max-w-2xl text-3xl font-semibold tracking-tight text-balance text-foreground sm:text-5xl lg:text-6xl">
                        Ship modern products faster with a coherent, production-ready design system.
                    </h1>
                    <p class="max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">
                        {{ appName }} gives you a robust foundation for e-commerce, dashboards, SaaS, and internal tools with reusable architecture,
                        type-safe contracts, and polished default UX.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                    <UiButton class="h-11 w-full px-6 sm:w-auto" as-child>
                        <Link :href="marketingPrimaryAction.href">
                            {{ marketingPrimaryAction.title }}
                        </Link>
                    </UiButton>
                    <UiButton variant="outline" class="h-11 w-full px-6 sm:w-auto" as-child>
                        <Link :href="marketingSecondaryAction.href">
                            {{ marketingSecondaryAction.title }}
                        </Link>
                    </UiButton>
                </div>
            </div>

            <div class="grid gap-4">
                <UiCard :class="theme.marketing.featureCard">
                    <UiCardHeader class="space-y-1 pb-2">
                        <UiCardTitle class="text-base">Design-system first</UiCardTitle>
                        <UiCardDescription>Consistent colors, spacing, typography, and component behavior by default.</UiCardDescription>
                    </UiCardHeader>
                    <UiCardContent class="text-sm text-muted-foreground">
                        Built on reusable UI primitives and token-driven styles to avoid duplicated one-off code.
                    </UiCardContent>
                </UiCard>

                <UiCard :class="theme.marketing.featureCard">
                    <UiCardHeader class="space-y-1 pb-2">
                        <UiCardTitle class="text-base">Scale-ready architecture</UiCardTitle>
                        <UiCardDescription>Clear boundaries between marketing, auth, and dashboard domains.</UiCardDescription>
                    </UiCardHeader>
                    <UiCardContent class="text-sm text-muted-foreground">
                        Role-aware navigation, namespaced components, and shared contracts keep growth maintainable.
                    </UiCardContent>
                </UiCard>

                <UiCard :class="theme.marketing.featureCard">
                    <UiCardHeader class="space-y-1 pb-2">
                        <UiCardTitle class="text-base">Secure and typed by default</UiCardTitle>
                        <UiCardDescription>Laravel policies and DTO-driven frontend types stay in sync.</UiCardDescription>
                    </UiCardHeader>
                    <UiCardContent class="text-sm text-muted-foreground">
                        Strong defaults reduce regressions and make feature delivery predictable over the long term.
                    </UiCardContent>
                </UiCard>
            </div>
        </section>
    </MarketingPageLayout>
</template>
