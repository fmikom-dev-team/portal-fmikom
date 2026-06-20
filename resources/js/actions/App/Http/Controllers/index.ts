import Auth from './Auth'
import Api from './Api'
import Settings from './Settings'
import QrVerificationController from './QrVerificationController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Api: Object.assign(Api, Api),
Settings: Object.assign(Settings, Settings),
QrVerificationController: Object.assign(QrVerificationController, QrVerificationController),
}

export default Controllers