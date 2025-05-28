# Sprint 1: Foundation & Core Components (TDD Focus)

## References

### Controllers
- Auth Controllers:
  - `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
  - `app/Http/Controllers/Auth/ConfirmablePasswordController.php`
  - `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`
  - `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
  - `app/Http/Controllers/Auth/NewPasswordController.php`
  - `app/Http/Controllers/Auth/PasswordController.php`
  - `app/Http/Controllers/Auth/PasswordResetLinkController.php`
  - `app/Http/Controllers/Auth/RegisteredUserController.php`
  - `app/Http/Controllers/Auth/VerifyEmailController.php`

### Vue Components
- Common Components:
  - `resources/js/Components/AppHeader.vue`
  - `resources/js/Components/AppFooter.vue`
  - `resources/js/Components/LoadingIndicator.vue`
  - `resources/js/Components/AuthForm.vue`

### Stores & Services
- Stores:
  - `resources/js/stores/auth.js` (or .ts)
  - `resources/js/stores/loading.js` (or .ts)
- Services:
  - `resources/js/services/api.js` (or .ts)
  - `resources/js/services/auth.js` (or .ts)

### Planning Docs
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-stores-services.md
- Planning: web-planning/common-components.md
- Planning: web-planning/stores-services.md

### E2E Tests
- `cypress/e2e/auth/authentication.cy.js`
- `cypress/e2e/auth/login.cy.js`
- `cypress/e2e/auth/registration.cy.js`
- `cypress/e2e/common/components.cy.js`
- `cypress/e2e/app.cy.js`

## Testing Goals
- [x] Setup Cypress for E2E testing (basic configuration and test file created)
- [x] Write unit tests for:
  - [x] Auth Store
  - [x] API Service
  - [x] AppHeader component (8 tests passing)
  - [x] AppFooter component (4 tests passing)
- [x] Create test data fixtures

## Implementation Tasks
1. **Common Components**:
   - [x] Create AppHeader (with auth state)
   - [x] Create AppFooter
   - [x] Implement LoadingIndicator
   - [x] Implement AuthForm (19/19 tests passing)

2. **State Management**:
   - [x] Implement Auth Store (7/7 tests passing)
   - [x] Implement Loading Store (4/4 tests passing)
   - [x] Implement Translation Store (100% coverage)

3. **Services**:
   - [x] Configure API Service (Axios)
   - [x] Implement Auth Service (8/8 tests passing)
   - [x] Implement Translation Service (100% coverage)

4. **Testing Infrastructure**:
   - [x] Configure test environment (Vitest + Vue Test Utils)
   - [x] Set up CI/CD test reporting (Vitest coverage)
   - [x] Write unit tests for all stores
   - [x] Write unit tests for all services
   - [x] Write unit tests for common components
   - [x] Implement E2E tests for all auth flows (6/6 passing)

5. **Translation Infrastructure**:
   - [x] Install and configure Vue I18n
   - [x] Create translation file structure
   - [x] Implement comprehensive pt-BR translations
   - [x] Set up extensible language switching mechanism
   - [x] Implement Laravel localization
   - [x] Configure API Accept-Language header
   - [x] Add tests for translation functionality (100% coverage)

## Test Fixes
- [x] Fixed Pinia initialization in auth service tests
- [x] Fixed AppHeader loading state test
- [x] Fixed route mocking in AppHeader tests
- [x] Fixed AuthForm mobile input issues
- [x] Achieved 100% coverage on stores
- [x] Achieved 90%+ coverage on services
- [x] All 40 tests passing
- [x] Fixed AuthForm validation tests
- [x] Implemented proper server validation error display

## Sprint Completion
✅ All sprint goals achieved on 2025-05-24
✅ All acceptance criteria met
✅ Ready for sprint review

## Acceptance Criteria
- All tests pass in isolation and together
- Core components render properly
- Auth flow works with mocked backend
- Loading states are managed properly
