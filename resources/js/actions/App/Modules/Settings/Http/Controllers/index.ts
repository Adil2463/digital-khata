import ProfileController from './ProfileController'
import PasswordController from './PasswordController'

const Controllers = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    PasswordController: Object.assign(PasswordController, PasswordController),
}

export default Controllers