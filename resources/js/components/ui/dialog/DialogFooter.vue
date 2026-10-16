<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>

<template>
  <div
    data-slot="dialog-footer"
    :class="cn(appTheme.dialog.footer, props.class)"
  >
    <slot />
  </div>
</template>
