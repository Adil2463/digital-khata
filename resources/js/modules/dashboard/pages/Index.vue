<script setup lang="ts">
    import type { DashboardPageData } from '@/types/app-data'

    const props = defineProps<DashboardPageData>()

    const breadcrumbs = buildDashboardBreadcrumbs()
    const user = useAuthUser({ required: true, context: 'dashboard page' })
    const shop = useCurrentShop()

    const greeting = computed(() => `Assalam-o-Alaikum, ${user.value.name}`)
    const todayLine = computed(() => {
        const currentShop = shop.value

        return currentShop === null ? '' : `${currentShop.name} · ${formatShopDate(props.shopTime, currentShop.timezone, { weekday: true })}`
    })
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 p-3 sm:p-4">
            <div class="mt-2 sm:mt-4">
                <Heading :title="greeting" :description="todayLine" />
            </div>

            <UiCard class="max-w-3xl">
                <UiCardContent>
                    <EmptyState
                        icon="folder"
                        title="Customer khata is on its way"
                        description="Customers, udhaar and wasooli will appear here in the next update. Until then, use the menu for your account and shop settings."
                    />
                </UiCardContent>
            </UiCard>
        </div>
    </AppLayout>
</template>
