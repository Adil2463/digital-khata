<script setup lang="ts">
    const theme = appTheme

    const { isAuthenticated, marketingPrimaryAction, marketingPrimaryItems, marketingSecondaryAction } = useNavigation()

    const hasPrimaryNavigation = computed(() => marketingPrimaryItems.value.length > 1)
    const marketingHomeHref = marketingRoutes.home.url()
</script>

<template>
    <header class="sticky top-0 z-50 px-2 pt-2 sm:px-4 sm:pt-3 lg:px-8">
        <div class="mx-auto w-full max-w-7xl">
            <div :class="[theme.navigation.marketingHeader, theme.surface.glass]">
                <Link :href="marketingHomeHref" :class="theme.navigation.marketingLogo">
                    <AppLogo />
                </Link>

                <nav v-if="hasPrimaryNavigation" class="hidden items-center gap-1.5 md:flex">
                    <UiButton
                        v-for="item in marketingPrimaryItems"
                        :key="item.title"
                        variant="ghost"
                        class="h-9 rounded-lg px-3.5"
                        :class="item.isActive ? theme.navigation.marketingActive : theme.navigation.marketingIdle"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </UiButton>
                </nav>

                <div class="ml-auto hidden items-center gap-2 md:flex">
                    <UiButton variant="ghost" class="h-9 rounded-lg px-4 text-muted-foreground hover:text-foreground" as-child>
                        <Link :href="marketingSecondaryAction.href">
                            {{ marketingSecondaryAction.title }}
                        </Link>
                    </UiButton>
                    <UiButton class="h-9 rounded-lg px-4 shadow-sm" as-child>
                        <Link :href="marketingPrimaryAction.href">
                            {{ marketingPrimaryAction.title }}
                        </Link>
                    </UiButton>
                </div>

                <div class="ml-auto md:hidden">
                    <UiSheet>
                        <UiSheetTrigger as-child>
                            <UiButton variant="ghost" size="icon" class="h-9 w-9 rounded-lg border border-border/70 bg-background">
                                <IconLucideMenu class="size-5" />
                                <span class="sr-only">Open navigation</span>
                            </UiButton>
                        </UiSheetTrigger>
                        <UiSheetContent side="right" class="w-full max-w-[20rem] px-5 py-7 sm:px-6 sm:py-8">
                            <UiSheetTitle class="sr-only">Marketing navigation</UiSheetTitle>
                            <div class="flex flex-col gap-6">
                                <Link :href="marketingHomeHref" class="inline-flex w-fit">
                                    <AppLogo />
                                </Link>

                                <nav class="grid gap-2">
                                    <UiButton
                                        v-for="item in marketingPrimaryItems"
                                        :key="item.title"
                                        variant="ghost"
                                        class="h-10 justify-start px-3"
                                        :class="item.isActive ? theme.navigation.marketingActive : theme.navigation.marketingIdle"
                                        as-child
                                    >
                                        <Link :href="item.href">
                                            {{ item.title }}
                                        </Link>
                                    </UiButton>
                                </nav>

                                <div class="grid gap-2 border-t border-border/70 pt-6">
                                    <UiButton :variant="isAuthenticated ? 'outline' : 'ghost'" class="h-10 justify-start px-3" as-child>
                                        <Link :href="marketingSecondaryAction.href">
                                            {{ marketingSecondaryAction.title }}
                                        </Link>
                                    </UiButton>
                                    <UiButton class="h-10 justify-start px-3" as-child>
                                        <Link :href="marketingPrimaryAction.href">
                                            {{ marketingPrimaryAction.title }}
                                        </Link>
                                    </UiButton>
                                </div>
                            </div>
                        </UiSheetContent>
                    </UiSheet>
                </div>
            </div>
        </div>
    </header>
</template>
