import { useConnectionStatus } from '@laravel/echo-vue'
import type { ConnectionStatus } from 'laravel-echo'

export type RealtimeConnectionStatus = ConnectionStatus

export function useRealtimeConnection() {
    const status = useConnectionStatus()

    return {
        status,
        isConnected: computed(() => status.value === 'connected'),
        isConnecting: computed(() => status.value === 'connecting' || status.value === 'reconnecting'),
        hasFailed: computed(() => status.value === 'failed')
    }
}
