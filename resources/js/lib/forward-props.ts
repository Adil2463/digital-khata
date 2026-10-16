import { useEmitAsProps, useForwardProps, useForwardPropsEmits } from 'reka-ui'
import type { ComputedRef, MaybeRefOrGetter } from 'vue'
import type { OptionalWithoutUndefined } from './utils'

// reka-ui already drops every key whose value is `undefined` while forwarding, but it
// types the result with all of them present. Under `exactOptionalPropertyTypes` that
// shape cannot be spread onto a child declaring the same props as optional, so a
// vendored primitive has to reconcile the two somewhere. Asserting `as Partial<...>`
// hides missing required props, and re-running `omitUndefinedProps` over the result
// filters a second time for a value reka-ui already filtered. These adapters instead
// restate reka-ui's existing runtime contract in the type system: the assertions live
// here once, and forwarding costs nothing beyond what reka-ui already does.
//
// Both read the calling component's own instance to decide which keys were actually
// supplied, so they only ever forward that component's props (optionally narrowed with
// `reactiveOmit`). Sanitising an unrelated object is what `omitUndefinedProps` is for.

type ForwardableProps = Record<string, unknown>
type EmitFn = (...args: never[]) => void

/** reka-ui keeps its emit-to-prop mapping internal, so it is read back off the helper it exports. */
type EmitAsProps<TEmit extends EmitFn> = ReturnType<typeof useEmitAsProps<string, TEmit>>

/** Forward props to a reka-ui primitive with exact optional property types. */
export function useForwardedProps<T extends object>(props: MaybeRefOrGetter<T>): ComputedRef<OptionalWithoutUndefined<T>> {
    return useForwardProps(props as MaybeRefOrGetter<ForwardableProps>) as ComputedRef<OptionalWithoutUndefined<T>>
}

/** Forward props and emit handlers to a reka-ui primitive with exact optional property types. */
export function useForwardedPropsEmits<T extends object, TEmit extends EmitFn>(
    props: MaybeRefOrGetter<T>,
    emit: TEmit
): ComputedRef<OptionalWithoutUndefined<T> & EmitAsProps<TEmit>> {
    return useForwardPropsEmits(props as MaybeRefOrGetter<ForwardableProps>, emit as never) as ComputedRef<
        OptionalWithoutUndefined<T> & EmitAsProps<TEmit>
    >
}
