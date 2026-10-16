import type { PresenceMemberData, UserChangedBroadcastData, UserManagementNotificationData, UsersListChangedBroadcastData } from '@/types/app-data'
import { SharedRealtimeChannel, UsersRealtimeChannel, UsersRealtimeEvent } from '@/types/app-data'
import type { MaybeRefOrGetter } from 'vue'

interface UseUsersIndexRealtimeOptions {
    currentUserId: MaybeRefOrGetter<number>
    onListChanged?: (payload: UsersListChangedBroadcastData) => void
}

export function useUsersIndexRealtime(options: UseUsersIndexRealtimeOptions) {
    const { info } = useToast()
    const resolvedCurrentUserId = computed(() => toValue(options.currentUserId))

    const listSubscription = useRealtimeEvent<UsersListChangedBroadcastData>({
        channel: resolveRealtimeChannel(UsersRealtimeChannel.Index),
        event: UsersRealtimeEvent.ListChanged,
        onMessage: options.onListChanged
    })

    const presence = useRealtimePresence<PresenceMemberData>({
        channel: resolveRealtimeChannel(UsersRealtimeChannel.Presence),
        memberKey: (member) => member.id
    })

    const notificationSubscription = useRealtimeNotification<UserManagementNotificationData>({
        channel: resolveRealtimeChannel(SharedRealtimeChannel.UserNotifications, {
            userId: resolvedCurrentUserId.value
        }),
        dependencies: [resolvedCurrentUserId.value],
        toast: (payload) => {
            info({
                title: payload.title,
                description: payload.description
            })
        }
    })

    const activeCollaboratorCount = computed(() => presence.members.value.length)

    return {
        listSubscription,
        notificationSubscription,
        presence,
        activeCollaboratorCount
    }
}

export function useUserRealtime(userId: MaybeRefOrGetter<number>, onMessage?: (payload: UserChangedBroadcastData) => void) {
    const resolvedUserId = computed(() => toValue(userId))

    return useRealtimeEvent<UserChangedBroadcastData>({
        channel: resolveRealtimeChannel(UsersRealtimeChannel.User, {
            userId: resolvedUserId.value
        }),
        event: UsersRealtimeEvent.UserChanged,
        dependencies: [resolvedUserId.value],
        onMessage
    })
}
