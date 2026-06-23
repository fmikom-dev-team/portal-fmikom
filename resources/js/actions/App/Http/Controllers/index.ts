import Api from './Api'
import QrVerificationController from './QrVerificationController'
const Controllers = {
    Api: Object.assign(Api, Api),
QrVerificationController: Object.assign(QrVerificationController, QrVerificationController),
}

export default Controllers