<script setup lang="ts">
    import ProfileController from '@/actions/App/Modules/Settings/Http/Controllers/ProfileController'
    import { deleteAccountFormContract, type DeleteAccountFormValues } from '../forms/delete-account-form-schema'

    const theme = appTheme

    const isOpen = ref(false)

    const { form, fields, submit } = useSchemaResourceForm<DeleteAccountFormValues>(deleteAccountFormContract)

    const closeModal = () => {
        form.clearErrors()
        form.reset()
        isOpen.value = false
    }

    const deleteUser = () => {
        submit(ProfileController.destroy(), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            },
            onError: () => {
                form.reset('password')
            },
            onFinish: () => {
                form.reset('password')
            }
        })
    }
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Delete account" description="Delete your account and all of its resources" />
        <div :class="[theme.surface.danger, 'space-y-4 p-4']">
            <div class="relative space-y-0.5 text-destructive">
                <p class="font-medium">Warning</p>
                <p class="text-sm">Please proceed with caution, this cannot be undone.</p>
            </div>
            <BaseButton variant="destructive" label="Delete account" @click="isOpen = true" />

            <BaseDialogBaseConfirmDialog
                :open="isOpen"
                title="Are you sure you want to delete your account?"
                description="Once your account is deleted, all resources and data will be permanently deleted. Please enter your password to confirm."
                confirm-label="Delete account"
                :processing="form.processing"
                :show-footer="false"
                @update:open="isOpen = $event"
                @confirm="deleteUser"
            >
                <div class="pt-4">
                    <BaseFormsBaseFormRenderer
                        :model="form"
                        :fields="fields"
                        :errors="form.errors"
                        :processing="form.processing"
                        submit-label="Delete account"
                        :show-cancel="true"
                        cancel-label="Cancel"
                        @submit="deleteUser"
                        @cancel="closeModal"
                    />
                </div>
            </BaseDialogBaseConfirmDialog>
        </div>
    </div>
</template>
