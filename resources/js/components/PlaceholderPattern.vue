<script setup lang="ts">
    // `useId()` yields the same value on the server and on the client, so the `url(#…)`
    // reference hydrates without a mismatch. `Math.random()` cannot: the SSR render and
    // the client render each roll their own, and every page here is server-rendered.
    const patternId = `pattern-${useId()}`
</script>

<template>
    <svg class="absolute inset-0 size-full stroke-foreground/20" fill="none">
        <defs>
            <pattern :id="patternId" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                <path d="M-1 5L5 -1M3 9L8.5 3.5" stroke-width="0.5"></path>
            </pattern>
        </defs>
        <rect stroke="none" :fill="`url(#${patternId})`" width="100%" height="100%"></rect>
    </svg>
</template>
