<script setup lang="ts">
    import ProfileController from '@/actions/App/Modules/Settings/Http/Controllers/ProfileController'
    import type { ProfilePageData } from '@/types/app-data'
    import { createProfileFormDefaults, profileFormContract, type ProfileFormValues } from '../forms/profile-form-schema'

    const theme = appTheme

    defineProps<ProfilePageData>()

    const breadcrumbs = buildSettingsProfileBreadcrumbs()
    const resendVerificationHref = authRoutes.verification.send.url()

    const user = useAuthUser({ required: true, context: 'profile settings page' })

    const { form, fields, submit } = useSchemaResourceForm<ProfileFormValues>(
        profileFormContract,
        createProfileFormDefaults({
            name: user.value.name,
            email: user.value.email
        })
    )

    const submitForm = () => {
        submit(ProfileController.update(), {
            preserveScroll: true
        })
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <BaseFormsBaseFormRenderer
                    :model="form"
                    :fields="fields"
                    :errors="form.errors"
                    :processing="form.processing"
                    submit-label="Save"
                    @submit="submitForm"
                />

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link :href="resendVerificationHref" method="post" as="button" :class="theme.link">
                            Click here to resend the verification email.
                        </Link>
                    </p>

                    <div v-if="status === 'verification-link-sent'" :class="['mt-2', theme.feedback.status, theme.status.success]">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <SavedFeedback :show="form.recentlySuccessful" />
            </div>

            <SettingsDeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
