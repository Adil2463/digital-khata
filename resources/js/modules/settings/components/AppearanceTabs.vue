<script setup lang="ts">
    import { Appearance } from '@/types/app-data'

    const theme = appTheme

    const { appearance, updateAppearance } = useAppearance()

    const isDarkMode = computed({
        get: () => appearance.value === Appearance.Dark,
        set: (value: boolean) => {
            updateAppearance(value ? Appearance.Dark : Appearance.Light)
        }
    })

    // The tab triggers only ever carry the two enum values, but the emitted type is
    // widened by the primitive, so narrow rather than cast.
    const selectAppearance = (value: unknown): void => {
        if (value === Appearance.Light || value === Appearance.Dark) {
            updateAppearance(value)
        }
    }
</script>

<template>
    <div class="space-y-4">
        <UiTabs :model-value="appearance" @update:model-value="selectAppearance" class="space-y-3">
            <UiTabsList class="w-full sm:w-auto">
                <UiTabsTrigger :value="Appearance.Light" class="min-w-28">
                    <IconLucideSun class="mr-2 size-4" />
                    Light
                </UiTabsTrigger>
                <UiTabsTrigger :value="Appearance.Dark" class="min-w-28">
                    <IconLucideMoon class="mr-2 size-4" />
                    Dark
                </UiTabsTrigger>
            </UiTabsList>
        </UiTabs>

        <div :class="theme.field.toggle">
            <div>
                <p class="text-sm font-medium">Dark mode toggle</p>
                <p class="text-xs text-muted-foreground">Switch instantly between light and dark appearance.</p>
            </div>
            <UiSwitch v-model="isDarkMode" />
        </div>
    </div>
</template>
