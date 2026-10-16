<script setup lang="ts">
    import AuthenticatedSessionController from '@/actions/App/Modules/Auth/Http/Controllers/AuthenticatedSessionController'
    import type { LoginPageData } from '@/types/app-data'
    import { loginFormContract, type LoginFormValues } from '../forms/login-form-schema'

    const theme = appTheme

    defineProps<LoginPageData>()

    const { form, fields, submit } = useSchemaResourceForm<LoginFormValues>(loginFormContract)
    const passwordRequestHref = authRoutes.password.request.url()

    const submitForm = () => {
        submit(AuthenticatedSessionController.store(), {
            onFinish: () => {
                form.reset('password')
            }
        })
    }
</script>

<template>
    <AuthLayout title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" :class="['mb-4 text-center', theme.feedback.status, theme.status.success]">
            {{ status }}
        </div>

        <BaseFormsBaseFormRenderer
            :model="form"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Log in"
            @submit="submitForm"
        >
            <template #actions>
                <div class="space-y-4">
                    <BaseButton type="submit" full-width :loading="form.processing" label="Log in" />
                    <div class="flex items-center justify-between">
                        <TextLink v-if="canResetPassword" :href="passwordRequestHref" class="text-sm">Forgot password?</TextLink>
                    </div>
                </div>
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
