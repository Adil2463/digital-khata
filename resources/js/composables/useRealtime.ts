import { useEcho, useEchoModel, useEchoNotification, useEchoPresence, useEchoPublic } from '@laravel/echo-vue'
import type { BroadcastDriver } from 'laravel-echo'
import type { Ref } from 'vue'

export type RealtimeCacheKey = string | string[]

export type RealtimeNotification<TPayload> = TPayload & {
    id: string
    type: string
}

interface RealtimeReactionOptions<TPayload> {
    invalidateKeys?: RealtimeCacheKey[]
    updateCache?: ((payload: TPayload) => void) | undefined
    onMessage?: ((payload: TPayload) => void) | undefined
    toast?: ((payload: TPayload) => void) | undefined
}

interface UseRealtimeEventOptions<TPayload> extends RealtimeReactionOptions<TPayload> {
    channel: string
    event: string | string[]
    dependencies?: unknown[]
    visibility?: 'private' | 'public'
}

interface UseRealtimeModelOptions<TPayload> extends RealtimeReactionOptions<TPayload> {
    model: string
    identifier: string | number
    event?: string | string[]
    dependencies?: unknown[]
}

interface UseRealtimeNotificationOptions<TPayload> extends RealtimeReactionOptions<RealtimeNotification<TPayload>> {
    channel: string
    dependencies?: unknown[]
}

interface UseRealtimePresenceOptions<TMember, TPayload> extends RealtimeReactionOptions<TPayload> {
    channel: string
    event?: string | string[]
    dependencies?: unknown[]
    memberKey?: (member: TMember) => string | number
    onHere?: (members: TMember[]) => void
    onJoining?: (member: TMember) => void
    onLeaving?: (member: TMember) => void
}

const toArray = (value: string | string[]): string[] => (Array.isArray(value) ? value : [value])

const normalizeEventName = (eventName: string): string => (eventName.startsWith('.') ? eventName : `.${eventName}`)

const normalizeEventNames = (event: string | string[] | undefined): string | string[] | undefined => {
    if (event === undefined) {
        return undefined
    }

    const normalized = toArray(event).map((eventName) => normalizeEventName(eventName))

    return normalized.length === 1 ? normalized[0] : normalized
}

const applyRealtimeEffects = <TPayload>(payload: TPayload, options: RealtimeReactionOptions<TPayload>): void => {
    options.updateCache?.(payload)

    if (options.invalidateKeys !== undefined && options.invalidateKeys.length > 0) {
        invalidateApiQueryCache(...options.invalidateKeys)
    }

    options.toast?.(payload)
    options.onMessage?.(payload)
}

const removePresenceMember = <TMember>(members: TMember[], leavingMember: TMember, memberKey?: (member: TMember) => string | number): TMember[] => {
    if (memberKey === undefined) {
        const index = members.findIndex((member) => member === leavingMember)

        if (index === -1) {
            return members
        }

        return members.filter((_member, currentIndex) => currentIndex !== index)
    }

    const leavingKey = memberKey(leavingMember)

    return members.filter((member) => memberKey(member) !== leavingKey)
}

const upsertPresenceMember = <TMember>(members: TMember[], joiningMember: TMember, memberKey?: (member: TMember) => string | number): TMember[] => {
    if (memberKey === undefined) {
        return [...members, joiningMember]
    }

    const joiningKey = memberKey(joiningMember)
    const nextMembers = members.filter((member) => memberKey(member) !== joiningKey)
    nextMembers.push(joiningMember)

    return nextMembers
}

export function useRealtimeEvent<TPayload, TDriver extends BroadcastDriver = BroadcastDriver>(options: UseRealtimeEventOptions<TPayload>) {
    const handler = (payload: TPayload): void => {
        applyRealtimeEffects(payload, options)
    }

    const normalizedEvents = normalizeEventNames(options.event)

    if (options.visibility === 'public') {
        return useEchoPublic<TPayload, TDriver>(options.channel, normalizedEvents ?? [], handler, options.dependencies ?? [])
    }

    return useEcho<TPayload, TDriver>(options.channel, normalizedEvents ?? [], handler, options.dependencies ?? [], 'private')
}

export function useRealtimeModel<TPayload, TDriver extends BroadcastDriver = BroadcastDriver>(options: UseRealtimeModelOptions<TPayload>) {
    const handler = (payload: { model: TPayload; connection: string | null; queue: string | null; afterCommit: boolean }): void => {
        applyRealtimeEffects(payload.model, options)
    }

    const normalizedEvents = normalizeEventNames(options.event)

    return useEchoModel<TPayload, string, TDriver>(
        options.model,
        options.identifier,
        normalizedEvents as Parameters<typeof useEchoModel<TPayload, string, TDriver>>[2],
        handler,
        options.dependencies ?? []
    )
}

export function useRealtimeNotification<TPayload, TDriver extends BroadcastDriver = BroadcastDriver>(
    options: UseRealtimeNotificationOptions<TPayload>
) {
    const handler = (payload: RealtimeNotification<TPayload>): void => {
        applyRealtimeEffects(payload, options)
    }

    return useEchoNotification<TPayload, TDriver>(options.channel, handler, undefined, options.dependencies ?? [])
}

export function useRealtimePresence<TMember, TPayload = never, TDriver extends BroadcastDriver = BroadcastDriver>(
    options: UseRealtimePresenceOptions<TMember, TPayload>
) {
    const members = shallowRef<TMember[]>([])

    const subscription = useEchoPresence<TPayload, TDriver>(
        options.channel,
        normalizeEventNames(options.event) ?? [],
        (payload: TPayload) => applyRealtimeEffects(payload, options),
        options.dependencies ?? []
    )

    subscription.channel().here((presentMembers: unknown[]) => {
        members.value = [...(presentMembers as TMember[])]
        options.onHere?.(members.value)
    })

    subscription.channel().joining((joiningMember: unknown) => {
        members.value = upsertPresenceMember(members.value, joiningMember as TMember, options.memberKey)
        options.onJoining?.(joiningMember as TMember)
    })

    subscription.channel().leaving((leavingMember: unknown) => {
        members.value = removePresenceMember(members.value, leavingMember as TMember, options.memberKey)
        options.onLeaving?.(leavingMember as TMember)
    })

    return {
        members: readonly(members) as Readonly<Ref<TMember[]>>,
        leaveChannel: subscription.leaveChannel,
        leave: subscription.leave,
        stopListening: subscription.stopListening,
        listen: subscription.listen,
        channel: subscription.channel,
        whisper: <TWhisperPayload extends Record<string, unknown>>(event: string, payload: TWhisperPayload) => {
            subscription.channel().whisper(event, payload)
        }
    }
}
