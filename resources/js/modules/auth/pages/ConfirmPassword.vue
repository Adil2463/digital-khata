<script setup lang="ts">
    import ConfirmablePasswordController from '@/actions/App/Modules/Auth/Http/Controllers/ConfirmablePasswordController'
    import { confirmPasswordFormContract, type ConfirmPasswordFormValues } from '../forms/confirm-password-form-schema'

    const { form, fields, submit } = useSchemaResourceForm<ConfirmPasswordFormValues>(confirmPasswordFormContract)

    const submitForm = () => {
        submit(ConfirmablePasswordController.store(), {
            onFinish: () => {
                form.reset()
            }
        })
    }
</script>

<template>
    <AuthLayout title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

        <BaseFormsBaseFormRenderer
            :model="form"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Confirm Password"
            @submit="submitForm"
        >
            <template #actions>
                <BaseButton type="submit" full-width :loading="form.processing" label="Confirm Password" />
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
