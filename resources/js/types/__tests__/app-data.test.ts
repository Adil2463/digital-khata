import { expectTypeOf, it } from 'vitest'
import type { AppPageProps } from '..'
import type {
    PaginationData,
    SharedPageData,
    UserChangedBroadcastData,
    UserIndexData,
    UserManagementNotificationData,
    UsersIndexPageData,
    UserViewData
} from '../app-data'

it('preserves explicit nulls in backend response contracts', () => {
    expectTypeOf<AppPageProps['flash']['message']>().toEqualTypeOf<string | null>()
    expectTypeOf<AppPageProps>().toExtend<SharedPageData>()
    expectTypeOf<UserViewData['email_verified_at']>().toEqualTypeOf<string | null>()
    expectTypeOf<UserChangedBroadcastData['user']>().toEqualTypeOf<UserViewData | null>()
    expectTypeOf<UserManagementNotificationData['targetUserId']>().toEqualTypeOf<number | null>()
    expectTypeOf<UserIndexData['search']>().toEqualTypeOf<string | null>()
})

it('describes every paginated listing with the one shared envelope', () => {
    expectTypeOf<UsersIndexPageData>().toEqualTypeOf<{ items: UserViewData[]; pagination: PaginationData }>()
    expectTypeOf<PaginationData>().toEqualTypeOf<{ current_page: number; last_page: number; per_page: number; total: number }>()
})
