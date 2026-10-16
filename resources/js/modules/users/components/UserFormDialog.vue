<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import type { UserViewData } from '@/types/app-data'
    import { buildUserFormFields, createUserFormDefaults, type UserFormValues } from '../forms/user-form-schema'

    type UserFormMode = 'create' | 'edit'

    interface Props {
        open: boolean
        mode: UserFormMode
        currentUserId: number
        user?: UserViewData | null
    }

    const props = withDefaults(defineProps<Props>(), {
        user: null
    })

    const emit = defineEmits<{
        'update:open': [open: boolean]
        created: []
        updated: []
    }>()

    const { form, submit } = useResourceForm<UserFormValues>(createUserFormDefaults())

    const isEditMode = computed(() => props.mode === 'edit')
    const isSelf = computed(() => isEditMode.value && props.user?.id === props.currentUserId)
    const fields = computed(() => buildUserFormFields({ isEdit: isEditMode.value, isSelf: isSelf.value }))
    const title = computed(() => (isEditMode.value ? 'Edit account' : 'Add staff account'))
    const description = computed(() =>
        isEditMode.value ? 'Update the name, login, role, or password.' : 'The new account can log in straight away. It belongs to your shop.'
    )
    const submitLabel = computed(() => (isEditMode.value ? 'Save changes' : 'Create account'))

    const syncForm = (): void => {
        if (isEditMode.value && props.user !== null) {
            form.name = props.user.name
            form.email = props.user.email
            form.role = props.user.role
            form.password = ''
            form.password_confirmation = ''
            form.clearErrors()
            return
        }

        form.reset()
        form.clearErrors()
    }

    watch(
        () => [props.open, props.mode, props.user?.id],
        ([open]) => {
            if (!open) {
                return
            }

            syncForm()
        },
        { immediate: true }
    )

    const submitForm = (): void => {
        if (isEditMode.value) {
            if (props.user === null) {
                return
            }

            submit(UserController.update({ user: props.user.id }), {
                onSuccess: () => {
                    emit('updated')
                    emit('update:open', false)
                },
                onFinish: () => {
                    form.reset('password', 'password_confirmation')
                }
            })
            return
        }

        submit(UserController.store(), {
            resetOnSuccess: true,
            onSuccess: () => {
                emit('created')
                emit('update:open', false)
            },
            onFinish: () => {
                form.reset('password', 'password_confirmation')
            }
        })
    }
</script>

<template>
    <BaseDialog
        :open="open"
        :title="title"
        :description="description"
        :processing="form.processing"
        :show-footer="false"
        :show-cancel="false"
        max-width-class="sm:max-w-[460px]"
        @update:open="emit('update:open', $event)"
    >
        <BaseFormsBaseFormRenderer
            :model="form"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            :submit-label="submitLabel"
            cancel-label="Cancel"
            :show-cancel="true"
            @submit="submitForm"
            @cancel="emit('update:open', false)"
        />
    </BaseDialog>
</template>
