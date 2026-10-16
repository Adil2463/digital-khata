import type { ButtonSize, ButtonVariant } from '@/lib/theme'

export { default as Button } from "./Button.vue"
export { buttonStyles } from '@/lib/theme'

export interface ButtonVariants {
  variant: ButtonVariant
  size: ButtonSize
}
