<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import type { UserViewData } from '@/types/app-data'

    const theme = appTheme

    const props = defineProps<{
        open: boolean
        user: UserViewData
    }>()

    const emit = defineEmits<{
        'update:open': [open: boolean]
        changed: []
    }>()

    const { form, submit } = useResourceForm<{ account: string }>({ account: '' })

    const deactivating = computed(() => !isAccountDeactivated(props.user))
    const roleLabel = computed(() => userRoleLabel(props.user.role))
    const copy = computed(() =>
        deactivating.value
            ? {
                  title: 'Deactivate account',
                  description: `${props.user.name} will be signed out on every device and cannot log in until reactivated. Their history is kept.`,
                  confirmLabel: 'Deactivate'
              }
            : {
                  title: 'Reactivate account',
                  description: `${props.user.name} will be able to log in again with their existing password.`,
                  confirmLabel: 'Reactivate'
              }
    )

    const handleConfirm = (): void => {
        const route = deactivating.value ? UserController.deactivate({ user: props.user.id }) : UserController.reactivate({ user: props.user.id })

        submit(route, {
            onSuccess: () => {
                emit('changed')
                emit('update:open', false)
            }
        })
    }
</script>

<template>
    <BaseDialogBaseConfirmDialog
        :open="open"
        :title="copy.title"
        :description="copy.description"
        :confirm-label="copy.confirmLabel"
        :confirm-variant="deactivating ? 'destructive' : 'default'"
        :processing="form.processing"
        @confirm="handleConfirm"
        @update:open="emit('update:open', $event)"
    >
        <div class="space-y-3 py-4">
            <div class="flex items-center gap-4">
                <UserInfo :user="props.user" :show-email="true" />
                <span :class="[theme.feedback.pill, theme.feedback.pillTone.muted]">{{ roleLabel }}</span>
            </div>
            <p v-if="form.errors.account" role="alert" :class="[theme.feedback.status, theme.status.error]">
                {{ form.errors.account }}
            </p>
        </div>
    </BaseDialogBaseConfirmDialog>
</template>
