<script setup lang="ts">
    const breadcrumbItems = buildSettingsAppearanceBreadcrumbs()
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                <SettingsAppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
