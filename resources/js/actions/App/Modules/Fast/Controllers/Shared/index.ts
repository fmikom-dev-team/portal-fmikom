import NotificationController from './NotificationController'
import Approval from './Approval'

const Shared = {
    NotificationController: Object.assign(NotificationController, NotificationController),
    Approval: Object.assign(Approval, Approval),
}

export default Shared