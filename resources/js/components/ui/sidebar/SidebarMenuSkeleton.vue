<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Skeleton } from '@/components/ui/skeleton'
import { useId, type HTMLAttributes } from 'vue'

const props = defineProps<{
  showIcon?: boolean
  class?: HTMLAttributes['class']
}>()

// The varied width is decorative, but `Math.random()` would roll a different value on
// the server than on the client and break hydration. `useId()` is stable across both,
// so deriving from it keeps the variety without the mismatch.
const instanceId = useId()
const seed = [...instanceId].reduce((total, character) => total + character.charCodeAt(0), 0)
const width = `${(seed % 40) + 50}%`
</script>

<template>
  <div
    data-slot="sidebar-menu-skeleton"
    data-sidebar="menu-skeleton"
    :class="cn('flex h-8 items-center gap-2 rounded-md px-2', props.class)"
  >
    <Skeleton
      v-if="showIcon"
      class="size-4 rounded-md"
      data-sidebar="menu-skeleton-icon"
    />

    <Skeleton
      class="h-4 max-w-(--skeleton-width) flex-1"
      data-sidebar="menu-skeleton-text"
      :style="{ '--skeleton-width': width }"
    />
  </div>
</template>
