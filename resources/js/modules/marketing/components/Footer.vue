<script setup lang="ts">
    const currentYear = new Date().getFullYear()
    const { marketingFooterGroups } = useNavigation()
    const marketingHomeHref = marketingRoutes.home.url()
</script>

<template>
    <footer class="border-t border-border/70 bg-background/95">
        <div
            class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-10 sm:px-6 sm:py-12 md:grid-cols-2 lg:grid-cols-[minmax(0,1.3fr)_repeat(3,minmax(0,1fr))] lg:px-8"
        >
            <div class="space-y-4">
                <Link :href="marketingHomeHref" class="inline-flex">
                    <AppLogo />
                </Link>
                <p class="max-w-sm text-sm leading-relaxed text-muted-foreground">
                    A premium Laravel + Vue starter architecture optimized for maintainability, performance, and scalable product delivery.
                </p>
            </div>

            <section v-for="group in marketingFooterGroups" :key="group.title">
                <h2 class="text-sm font-semibold text-foreground">{{ group.title }}</h2>
                <ul class="mt-4 grid gap-2.5">
                    <li v-for="item in group.items" :key="item.title">
                        <Link :href="item.href" class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            {{ item.title }}
                        </Link>
                    </li>
                </ul>
            </section>
        </div>

        <div class="border-t border-border/70">
            <div
                class="mx-auto flex w-full max-w-7xl flex-col gap-1 px-4 py-4 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"
            >
                <p>&copy; {{ currentYear }} Laravel Starter Kit</p>
                <p>Built for consistent UX, robust architecture, and faster delivery.</p>
            </div>
        </div>
    </footer>
</template>
