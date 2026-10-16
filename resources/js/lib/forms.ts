import { isObjectRecord } from '@/lib/utils'
import type { FormFieldSchema } from '@/types/base-ui'
import type { FormDataType } from '@inertiajs/core'

export type FormErrorMap<TForm extends object> = Partial<Record<Extract<keyof TForm, string>, string>>

export interface FormContract<TForm extends FormDataType<TForm>> {
    defaults: () => TForm
    fields: () => Array<FormFieldSchema<TForm>>
    normalizeErrors?: (errors: unknown) => FormErrorMap<TForm>
}

export type FormValuesFromData<
    TData extends object,
    TOverrides extends object = Record<never, never>,
    TOmittedKeys extends keyof TData = never
> = Omit<TData, TOmittedKeys | keyof TOverrides> & TOverrides

export const mapInertiaFormErrors = <TForm extends object>(errors: unknown): FormErrorMap<TForm> => {
    if (!isObjectRecord(errors)) {
        return {}
    }

    const mapped: FormErrorMap<TForm> = {}

    Object.entries(errors).forEach(([fieldName, fieldError]) => {
        if (typeof fieldError === 'string' && fieldError.trim() !== '') {
            mapped[fieldName as Extract<keyof TForm, string>] = fieldError
        }
    })

    return mapped
}

export const defineFormContract = <TForm extends FormDataType<TForm>>(contract: FormContract<TForm>): FormContract<TForm> => contract

export const defineFormFields = <TForm extends object>(fields: Array<FormFieldSchema<TForm>>): Array<FormFieldSchema<TForm>> => fields
