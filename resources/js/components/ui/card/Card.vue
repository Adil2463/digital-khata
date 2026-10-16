<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        appTheme.surface.card,
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
