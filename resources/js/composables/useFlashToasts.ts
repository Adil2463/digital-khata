export function useFlashToasts() {
    const page = useAppPage()
    const { success, error, info } = useToast()

    watch(
        () => [page.props.flash.message ?? '', page.props.flash.error ?? '', page.props.flash.status ?? ''] as const,
        ([message, messageError, status], previousValues) => {
            const previousMessage = previousValues?.[0] ?? ''
            const previousError = previousValues?.[1] ?? ''
            const previousStatus = previousValues?.[2] ?? ''

            if (message !== '' && message !== previousMessage) {
                success({ title: message })
            }

            if (messageError !== '' && messageError !== previousError) {
                error({ title: messageError })
            }

            if (status !== '' && status !== previousStatus) {
                info({ title: status })
            }
        },
        { immediate: true }
    )
}
