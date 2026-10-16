<script setup lang="ts">
    import PasswordController from '@/actions/App/Modules/Settings/Http/Controllers/PasswordController'
    import { passwordFormContract, type PasswordFormValues } from '../forms/password-form-schema'

    const breadcrumbItems = buildSettingsPasswordBreadcrumbs()

    const { form, fields, submit } = useSchemaResourceForm<PasswordFormValues>(passwordFormContract)

    const updatePassword = () => {
        submit(PasswordController.update(), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
            },
            onError: (errors) => {
                if (errors.password !== undefined) {
                    form.reset('password', 'password_confirmation')
                }

                if (errors.current_password !== undefined) {
                    form.reset('current_password')
                }
            }
        })
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <BaseFormsBaseFormRenderer
                    :model="form"
                    :fields="fields"
                    :errors="form.errors"
                    :processing="form.processing"
                    submit-label="Save password"
                    @submit="updatePassword"
                />

                <SavedFeedback :show="form.recentlySuccessful" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
