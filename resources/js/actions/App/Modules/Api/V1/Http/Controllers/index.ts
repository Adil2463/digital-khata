import MeController from './MeController'
import AdminUserController from './AdminUserController'

const Controllers = {
    MeController: Object.assign(MeController, MeController),
    AdminUserController: Object.assign(AdminUserController, AdminUserController),
}

export default Controllers