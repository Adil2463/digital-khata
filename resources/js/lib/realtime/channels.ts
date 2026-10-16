export type RealtimeChannelParameter = boolean | number | string | { valueOf(): string | number }

const normalizeParameter = (key: string, value: RealtimeChannelParameter): string => {
    if (typeof value === 'boolean') {
        return value ? 'true' : 'false'
    }

    if (typeof value === 'number' || typeof value === 'string') {
        return String(value)
    }

    const resolved = value.valueOf()

    if (typeof resolved === 'number' || typeof resolved === 'string') {
        return String(resolved)
    }

    throw new Error(`Realtime channel parameter "${key}" must resolve to a string or number.`)
}

export const resolveRealtimeChannel = (pattern: string, parameters: Record<string, RealtimeChannelParameter> = {}): string => {
    return pattern.replace(/\{([^}]+)\}/g, (_match, parameter: string) => {
        if (!(parameter in parameters)) {
            throw new Error(`Missing realtime channel parameter "${parameter}".`)
        }

        return normalizeParameter(parameter, parameters[parameter] as RealtimeChannelParameter)
    })
}
