<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DialogClose, DialogContent, type DialogContentEmits, type DialogContentProps, DialogOverlay, DialogPortal } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DialogContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DialogContentEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
  <DialogPortal>
    <DialogOverlay
      :class="cn(appTheme.overlay, 'grid place-items-center overflow-y-auto')"
    >
      <DialogContent
        :class="
          cn(
            appTheme.dialog.scrollContent,
            props.class,
          )
        "
        v-bind="forwarded"
        @pointer-down-outside="(event) => {
          const originalEvent = event.detail.originalEvent;
          const target = originalEvent.target as HTMLElement;
          if (originalEvent.offsetX > target.clientWidth || originalEvent.offsetY > target.clientHeight) {
            event.preventDefault();
          }
        }"
      >
        <slot />

        <DialogClose
          :class="appTheme.dialog.close"
        >
          <IconLucideX class="size-4" />
          <span class="sr-only">Close</span>
        </DialogClose>
      </DialogContent>
    </DialogOverlay>
  </DialogPortal>
</template>
