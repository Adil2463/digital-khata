import me from './me'
import admin from './admin'

const v1 = {
    me: Object.assign(me, me),
    admin: Object.assign(admin, admin),
}

export default v1