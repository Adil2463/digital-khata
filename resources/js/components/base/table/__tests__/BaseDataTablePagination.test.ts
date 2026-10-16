import { mount } from '@vue/test-utils'
import { expect, it } from 'vitest'
import BaseDataTablePagination from '../BaseDataTablePagination.vue'

it('emits one page change for each pagination interaction', async () => {
    const wrapper = mount(BaseDataTablePagination, { props: { currentPage: 2, totalPages: 4, totalItems: 60, itemsPerPage: 15 } })
    await wrapper.get('[aria-label="Go to next page"]').trigger('click')
    expect(wrapper.emitted('pageChange')).toEqual([[3]])
    await wrapper.get('[aria-label="Go to previous page"]').trigger('click')
    expect(wrapper.emitted('pageChange')).toEqual([[3], [1]])
    const fourthPage = wrapper.findAll('button').find((button) => button.text() === '4')
    expect(fourthPage).toBeDefined()
    await fourthPage?.trigger('click')
    expect(wrapper.emitted('pageChange')).toEqual([[3], [1], [4]])
    wrapper.unmount()
})
