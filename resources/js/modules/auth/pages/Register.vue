<script setup lang="ts">
    import RegisteredUserController from '@/actions/App/Modules/Auth/Http/Controllers/RegisteredUserController'
    import { registerFormContract, type RegisterFormValues } from '../forms/register-form-schema'

    const { form, fields, submit } = useSchemaResourceForm<RegisterFormValues>(registerFormContract)
    const loginHref = authRoutes.login.create.url()

    const submitForm = () => {
        submit(RegisteredUserController.store(), {
            onFinish: () => {
                form.reset('password', 'password_confirmation')
            }
        })
    }
</script>

<template>
    <AuthLayout title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <BaseFormsBaseFormRenderer :model="form" :fields="fields" :errors="form.errors" :processing="form.processing" @submit="submitForm">
            <template #actions>
                <div class="space-y-4">
                    <BaseButton type="submit" full-width :loading="form.processing" label="Create account" />
                    <div class="text-center text-sm text-muted-foreground">
                        Already have an account?
                        <TextLink :href="loginHref" class="underline underline-offset-4">Log in</TextLink>
                    </div>
                </div>
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
