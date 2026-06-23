import DashboardController from './DashboardController'
import SubmissionController from './SubmissionController'
import HistoryController from './HistoryController'
import LetterTypeController from './LetterTypeController'

const Dosen = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    SubmissionController: Object.assign(SubmissionController, SubmissionController),
    HistoryController: Object.assign(HistoryController, HistoryController),
    LetterTypeController: Object.assign(LetterTypeController, LetterTypeController),
}

export default Dosen