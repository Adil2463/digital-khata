<script setup lang="ts">
    const shop = useCurrentShop()
</script>

<template>
    <div class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground">
        <AppLogoIcon class="size-5" />
    </div>
    <div class="ml-1 grid min-w-0 flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold">Digital Khata</span>
        <span v-if="shop" class="truncate text-xs leading-tight text-muted-foreground">{{ shop.name }}</span>
    </div>
</template>
