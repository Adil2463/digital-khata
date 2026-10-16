<script setup lang="ts">
    import ShopSettingsController from '@/actions/App/Modules/Shop/Http/Controllers/ShopSettingsController'
    import type { ShopSettingsPageData } from '@/types/app-data'
    import { createShopSettingsFormValues, shopSettingsFormContract, type ShopSettingsFormValues } from '../forms/shop-settings-form-schema'

    const props = defineProps<ShopSettingsPageData>()

    const breadcrumbs = buildShopSettingsBreadcrumbs()

    const { form, fields, submit } = useSchemaResourceForm<ShopSettingsFormValues>(
        shopSettingsFormContract,
        createShopSettingsFormValues(props.settings)
    )

    // Formatted from server-supplied instants in the shop timezone, so the browser's own
    // clock and timezone never change what is shown.
    const businessDate = computed(() => formatShopDate(props.shopTime, props.timezone, { weekday: true }))
    const lastSaved = computed(() => (props.updatedAt === null ? null : formatShopDateTime(props.updatedAt, props.timezone)))

    const saveSettings = () => {
        submit(ShopSettingsController.update(), {
            preserveScroll: true,
            onSuccess: () => {
                // The server normalizes spacing; show exactly what was stored.
                form.defaults(createShopSettingsFormValues(props.settings))
                form.reset()
            }
        })
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Shop settings" />

        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 p-3 sm:p-4">
            <div class="mt-2 sm:mt-4">
                <Heading title="Shop settings" description="Dukaan ki maloomat: your shop's details. Only owners can see or change this page." />
            </div>

            <div class="grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <UiCard>
                    <UiCardHeader>
                        <UiCardTitle>Shop details</UiCardTitle>
                        <UiCardDescription>Customers see these on printed receipts and statements.</UiCardDescription>
                    </UiCardHeader>
                    <UiCardContent>
                        <BaseFormsBaseFormRenderer
                            :model="form"
                            :fields="fields"
                            :errors="form.errors"
                            :processing="form.processing"
                            submit-label="Save changes"
                            @submit="saveSettings"
                        />
                    </UiCardContent>
                </UiCard>

                <UiCard>
                    <UiCardHeader>
                        <UiCardTitle class="flex items-center gap-2">
                            <IconLucideCalendarClock class="size-4 text-muted-foreground" aria-hidden="true" />
                            Business time
                        </UiCardTitle>
                        <UiCardDescription>Khata dates and daily totals follow Pakistan time.</UiCardDescription>
                    </UiCardHeader>
                    <UiCardContent>
                        <dl class="grid gap-4 text-sm">
                            <div class="grid gap-0.5">
                                <dt class="text-muted-foreground">Timezone</dt>
                                <dd class="font-medium">{{ timezoneLabel }}</dd>
                                <dd class="text-xs text-muted-foreground">{{ timezone }}. Set for every shop in Pakistan; it cannot be changed.</dd>
                            </div>
                            <div class="grid gap-0.5">
                                <dt class="text-muted-foreground">Today's business date</dt>
                                <dd class="font-medium">{{ businessDate }}</dd>
                                <dd class="text-xs text-muted-foreground">
                                    A new day starts at 12:00 AM Pakistan time, whatever this computer's clock says.
                                </dd>
                            </div>
                            <div v-if="lastSaved !== null" class="grid gap-0.5">
                                <dt class="text-muted-foreground">Details last saved</dt>
                                <dd class="font-medium">{{ lastSaved }}</dd>
                            </div>
                        </dl>
                    </UiCardContent>
                </UiCard>
            </div>
        </div>
    </AppLayout>
</template>
