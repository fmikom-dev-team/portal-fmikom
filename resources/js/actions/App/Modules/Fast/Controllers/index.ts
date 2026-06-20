import Admin from './Admin'
import Shared from './Shared'
import Mahasiswa from './Mahasiswa'
import Dosen from './Dosen'
import Kaprodi from './Kaprodi'
import Dekan from './Dekan'

const Controllers = {
    Admin: Object.assign(Admin, Admin),
    Shared: Object.assign(Shared, Shared),
    Mahasiswa: Object.assign(Mahasiswa, Mahasiswa),
    Dosen: Object.assign(Dosen, Dosen),
    Kaprodi: Object.assign(Kaprodi, Kaprodi),
    Dekan: Object.assign(Dekan, Dekan),
}

export default Controllers