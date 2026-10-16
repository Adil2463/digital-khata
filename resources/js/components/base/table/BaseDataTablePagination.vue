<script setup lang="ts">
    const theme = appTheme

    interface Props {
        currentPage: number
        totalPages: number
        totalItems: number
        itemsPerPage: number
    }

    const props = defineProps<Props>()

    const emit = defineEmits<{
        pageChange: [page: number]
    }>()

    const pageNumbers = computed(() => {
        const pages: number[] = []
        const siblingCount = 2
        const safeTotalPages = Math.max(1, props.totalPages)
        const safeCurrentPage = Math.min(Math.max(1, props.currentPage), safeTotalPages)
        const start = Math.max(1, safeCurrentPage - siblingCount)
        const end = Math.min(safeTotalPages, safeCurrentPage + siblingCount)

        for (let page = start; page <= end; page += 1) {
            pages.push(page)
        }

        return pages
    })
</script>

<template>
    <div v-if="props.totalPages > 1" :class="theme.pagination.wrapper">
        <UiPagination
            class="min-w-max"
            :total="props.totalItems"
            :items-per-page="props.itemsPerPage"
            :page="props.currentPage"
            :sibling-count="2"
            show-edges
            aria-label="Table pagination"
            @update:page="emit('pageChange', $event)"
        >
            <UiPaginationContent>
                <UiPaginationPrevious :disabled="props.currentPage === 1" aria-label="Go to previous page">
                    <IconLucideChevronLeft class="size-4" />
                    <span class="sr-only">Previous page</span>
                </UiPaginationPrevious>

                <template v-for="page in pageNumbers" :key="page">
                    <UiPaginationItem :value="page" as-child>
                        <UiButton
                            :class="theme.pagination.page"
                            :variant="page === props.currentPage ? 'outline' : 'ghost'"
                            :aria-current="page === props.currentPage ? 'page' : undefined"
                            :disabled="page === props.currentPage"
                        >
                            {{ page }}
                        </UiButton>
                    </UiPaginationItem>
                </template>

                <UiPaginationNext :disabled="props.currentPage === props.totalPages" aria-label="Go to next page">
                    <IconLucideChevronRight class="size-4" />
                    <span class="sr-only">Next page</span>
                </UiPaginationNext>
            </UiPaginationContent>
        </UiPagination>
    </div>
</template>
