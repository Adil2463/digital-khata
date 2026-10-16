import type { FormContract, FormErrorMap } from '@/lib/forms'
import { mapInertiaFormErrors } from '@/lib/forms'
import type { InertiaRouteDefinition } from '@/utils/route'
import type { RouteDefinition } from '@/wayfinder'
import type { FormDataType } from '@inertiajs/core'
import type { ResourceSubmitOptions } from './useResourceForm'
import { useResourceForm } from './useResourceForm'

export function useSchemaResourceForm<TForm extends FormDataType<TForm>>(contract: FormContract<TForm>, initialValues?: TForm) {
    const { form, submit } = useResourceForm<TForm>(initialValues ?? contract.defaults())
    const fields = computed(() => contract.fields())

    const submitWithContract = (
        definition: RouteDefinition<'get' | 'post' | 'put' | 'patch' | 'delete'> | InertiaRouteDefinition,
        options: ResourceSubmitOptions<TForm> = {}
    ): void => {
        const resolveErrors = (errors: unknown): FormErrorMap<TForm> => {
            if (options.mapErrors !== undefined) {
                return options.mapErrors(errors)
            }

            if (contract.normalizeErrors !== undefined) {
                return contract.normalizeErrors(errors)
            }

            return mapInertiaFormErrors<TForm>(errors)
        }

        submit(definition, {
            ...options,
            mapErrors: resolveErrors
        })
    }

    return {
        form,
        fields,
        submit: submitWithContract
    }
}
