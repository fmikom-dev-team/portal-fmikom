/**
 * Centralized test credentials and selectors for Portal FMIKOM
 *
 * Credentials mirror PlaywrightSeeder.php constants.
 * Selectors use data-test attributes where available, then semantic HTML.
 */

// ── Test Credentials ──────────────────────────────────────────────────────────

export const TEST_USERS = {
    superAdmin: {
        email: 'playwright.superadmin@fmikom.test',
        password: 'Playwright@SuperAdmin1',
        name: 'Test Super Admin',
        type: 'super-admin',
    },
    mahasiswa: {
        email: 'playwright.mahasiswa@fmikom.test',
        password: 'Playwright@Mahasiswa1',
        name: 'Test Mahasiswa',
        type: 'mahasiswa',
    },
    dosen: {
        email: 'playwright.dosen@fmikom.test',
        password: 'Playwright@Dosen1',
        name: 'Test Dosen',
        type: 'dosen',
    },
    alumni: {
        email: 'playwright.alumni@fmikom.test',
        password: 'Playwright@Alumni1',
        name: 'Test Alumni',
        type: 'alumni',
    },
    pending: {
        email: 'playwright.pending@fmikom.test',
        password: 'Playwright@Pending1',
        name: 'Test Pending User',
        type: 'mahasiswa',
    },
    rejected: {
        email: 'playwright.rejected@fmikom.test',
        password: 'Playwright@Rejected1',
        name: 'Test Rejected User',
        type: 'mahasiswa',
    },
} as const;

// ── Routes ────────────────────────────────────────────────────────────────────

export const ROUTES = {
    home: '/',
    login: '/login',
    logout: '/logout',
    register: '/register',
    forgotPassword: '/forgot-password',
    dashboard: '/dashboard',
    portal: '/portal',
    settingsProfile: '/settings/profile',
    settingsSecurity: '/settings/security',
    settingsAppearance: '/settings/appearance',
    workos: '/workos',
    workosUsers: '/workos/users',
    workosOrganizations: '/workos/organizations',
    pagi: '/pagi',
    pagiAdmin: '/pagi/admin',
    wims: '/wims',
    portalAdmin: '/portal-admin',
    waitingRoom: '/waiting-room',

    // Testing endpoints
    testingPing: '/__testing/ping',
    testingSeed: '/__testing/seed',
    testingLogin: '/__testing/login',
    testingLogout: '/__testing/logout',
    testingMe: '/__testing/me',
} as const;

// ── Login Page Selectors ───────────────────────────────────────────────────────

export const LOGIN_SELECTORS = {
    emailInput: '#email',
    passwordInput: '#password',
    rememberCheckbox: '#remember',
    submitButton: '[data-test="login-button"]',
    errorMessage: 'text=Incorrect username or password',
    statusMessage: '.text-green-600',
} as const;

// ── Forgot Password Selectors ─────────────────────────────────────────────────

export const FORGOT_PASSWORD_SELECTORS = {
    emailInput: '#email',
    submitButton: '[data-test="email-password-reset-link-button"]',
    successMessage: '.text-green-600',
} as const;

// ── Dashboard Selectors ───────────────────────────────────────────────────────

export const DASHBOARD_SELECTORS = {
    // Main layout navigation
    navbar: 'nav',
    userMenu: '[data-test="user-menu"]',
    sidebar: 'aside',
    sidebarToggle: '[data-test="sidebar-toggle"]',

    // Module cards on portal page
    moduleCard: '[data-test="module-card"]',
    moduleCardLink: '[data-test="module-card-link"]',
} as const;

// ── WorkOs Admin Selectors ───────────────────────────────────────────────────

export const WORKOS_SELECTORS = {
    // Users panel
    usersTab: 'text=Users',
    usersTable: '[data-test="users-table"]',
    approveButton: '[data-test="approve-user-btn"]',
    rejectButton: '[data-test="reject-user-btn"]',
    searchInput: '[data-test="search-input"]',

    // Navigation sidebar
    navUsers: 'text=Users',
    navOrganizations: 'text=Organizations',
    navRoles: 'text=Roles',
} as const;

// ── Navigation Selectors ───────────────────────────────────────────────────────

export const NAV_SELECTORS = {
    mainNavigation: 'nav[aria-label="main navigation"]',
    breadcrumb: '[aria-label="breadcrumb"]',
    logoutButton: '[data-test="logout-button"]',
    profileLink: '[data-test="profile-link"]',
} as const;

// ── Responsive Breakpoints ─────────────────────────────────────────────────────

export const BREAKPOINTS = {
    '4k': { width: 3840, height: 2160 },
    '1920': { width: 1920, height: 1080 },
    '1440': { width: 1440, height: 900 },
    '1366': { width: 1366, height: 768 },
    '1280': { width: 1280, height: 800 },
    '1024': { width: 1024, height: 768 },
    tablet: { width: 768, height: 1024 },
    'mobile-large': { width: 390, height: 844 },
    'mobile-small': { width: 375, height: 667 },
} as const;
