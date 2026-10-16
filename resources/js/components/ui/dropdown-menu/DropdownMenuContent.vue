<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import { reactiveOmit } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DropdownMenuContent, type DropdownMenuContentEmits, type DropdownMenuContentProps, DropdownMenuPortal } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = withDefaults(
    defineProps<DropdownMenuContentProps & { class?: HTMLAttributes['class'] }>(),
    {
        sideOffset: 4,
    },
)
const emits = defineEmits<DropdownMenuContentEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
    <DropdownMenuPortal>
        <DropdownMenuContent data-slot="dropdown-menu-content" v-bind="forwarded"
            :class="cn(appTheme.floating.content, 'origin-(--reka-dropdown-menu-content-transform-origin)', props.class)">
            <slot />
        </DropdownMenuContent>
    </DropdownMenuPortal>
</template>
