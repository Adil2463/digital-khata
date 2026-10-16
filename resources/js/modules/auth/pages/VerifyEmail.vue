<script setup lang="ts">
    import EmailVerificationNotificationController from '@/actions/App/Modules/Auth/Http/Controllers/EmailVerificationNotificationController'
    import type { VerifyEmailPageData } from '@/types/app-data'

    const theme = appTheme

    defineProps<VerifyEmailPageData>()

    const { form, submit } = useResourceForm({})
    const logoutHref = authRoutes.logout.url()

    const submitForm = () => {
        submit(EmailVerificationNotificationController.store())
    }
</script>

<template>
    <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <div v-if="status === 'verification-link-sent'" :class="['mb-4 text-center', theme.feedback.status, theme.status.success]">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form class="space-y-6 text-center" @submit.prevent="submitForm">
            <BaseButton type="submit" variant="secondary" :loading="form.processing" label="Resend verification email" />
            <TextLink :href="logoutHref" method="post" as="button" class="mx-auto block text-sm">Log out</TextLink>
        </form>
    </AuthLayout>
</template>
