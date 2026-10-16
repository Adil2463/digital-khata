import type { FormErrorMap } from '@/lib/forms'
import { mapInertiaFormErrors } from '@/lib/forms'
import type { InertiaRouteDefinition } from '@/utils/route'
import type { RouteDefinition } from '@/wayfinder'
import type { FormDataType } from '@inertiajs/core'

type FormValues<T extends object> = FormDataType<T>

export interface ResourceSubmitOptions<TForm extends object> {
    preserveScroll?: boolean
    preserveState?: boolean
    resetOnSuccess?: true | Array<keyof TForm & string>
    onSuccess?: () => void
    onError?: (errors: FormErrorMap<TForm>) => void
    onFinish?: () => void
    mapErrors?: (errors: unknown) => FormErrorMap<TForm>
}

export function useResourceForm<TForm extends FormValues<TForm>>(initialValues: TForm) {
    const form = useForm(initialValues)

    const resetByPolicy = (resetOnSuccess?: true | Array<keyof TForm & string>): void => {
        if (resetOnSuccess === true) {
            form.reset()
            return
        }

        if (Array.isArray(resetOnSuccess) && resetOnSuccess.length > 0) {
            resetOnSuccess.forEach((field) => {
                form.reset(field as never)
            })
        }
    }

    const submit = (
        definition: RouteDefinition<'get' | 'post' | 'put' | 'patch' | 'delete'> | InertiaRouteDefinition,
        options: ResourceSubmitOptions<TForm> = {}
    ): void => {
        const routeDefinition = toInertiaRouteDefinition(definition)

        form.submit(routeDefinition.method, routeDefinition.url, {
            preserveScroll: options.preserveScroll ?? true,
            preserveState: options.preserveState ?? true,
            onSuccess: () => {
                resetByPolicy(options.resetOnSuccess)
                options.onSuccess?.()
            },
            onError: (errors) => {
                const mappedErrors = options.mapErrors !== undefined ? options.mapErrors(errors) : mapInertiaFormErrors<TForm>(errors)
                options.onError?.(mappedErrors)
            },
            onFinish: () => {
                options.onFinish?.()
            }
        })
    }

    return {
        form,
        submit
    }
}
