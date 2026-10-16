<script setup lang="ts">
import { useForwardedPropsEmits } from '@/lib/forward-props'
import type { HTMLAttributes } from 'vue'
import type { SwitchRootEmits, SwitchRootProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import { SwitchRoot, SwitchThumb } from 'reka-ui'

const props = defineProps<
    SwitchRootProps & {
        class?: HTMLAttributes['class']
    }
>()

const emits = defineEmits<SwitchRootEmits>()

const delegatedProps = reactiveOmit(props, 'class')
const forwarded = useForwardedPropsEmits(delegatedProps, emits)
</script>

<template>
    <SwitchRoot
        v-bind="forwarded"
        data-slot="switch"
        :class="
            cn(
                appTheme.field.switch,
                props.class,
            )
        "
    >
        <SwitchThumb
            data-slot="switch-thumb"
            :class="
                cn(
                    appTheme.field.switchThumb,
                )
            "
        />
    </SwitchRoot>
</template>
