import PwaController from './PwaController'
import Api from './Api'
import QrVerificationController from './QrVerificationController'
const Controllers = {
    PwaController: Object.assign(PwaController, PwaController),
Api: Object.assign(Api, Api),
QrVerificationController: Object.assign(QrVerificationController, QrVerificationController),
}

export default Controllers