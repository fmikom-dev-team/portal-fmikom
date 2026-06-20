import WorkOs from './WorkOs'
import Portal from './Portal'
import Coreportal from './Coreportal'
import Pagi from './Pagi'
import Wims from './Wims'
import Fast from './Fast'
import Trace from './Trace'
import Settings from './Settings'

const Modules = {
    WorkOs: Object.assign(WorkOs, WorkOs),
    Portal: Object.assign(Portal, Portal),
    Coreportal: Object.assign(Coreportal, Coreportal),
    Pagi: Object.assign(Pagi, Pagi),
    Wims: Object.assign(Wims, Wims),
    Fast: Object.assign(Fast, Fast),
    Trace: Object.assign(Trace, Trace),
    Settings: Object.assign(Settings, Settings),
}

export default Modules