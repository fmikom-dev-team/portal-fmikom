import DashboardController from './DashboardController'
import LetterController from './LetterController'
import ArchiveController from './ArchiveController'
import LetterIndexController from './LetterIndexController'
import HistoryController from './HistoryController'
import TemplateController from './TemplateController'
import CategoryController from './CategoryController'
import QrManageController from './QrManageController'
import GlobalSettingsController from './GlobalSettingsController'
const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
LetterController: Object.assign(LetterController, LetterController),
ArchiveController: Object.assign(ArchiveController, ArchiveController),
LetterIndexController: Object.assign(LetterIndexController, LetterIndexController),
HistoryController: Object.assign(HistoryController, HistoryController),
TemplateController: Object.assign(TemplateController, TemplateController),
CategoryController: Object.assign(CategoryController, CategoryController),
QrManageController: Object.assign(QrManageController, QrManageController),
GlobalSettingsController: Object.assign(GlobalSettingsController, GlobalSettingsController),
}

export default Admin