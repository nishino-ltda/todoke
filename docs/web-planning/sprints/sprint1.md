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
  - `resources/js/components/AppHeader.vue`
  - `resources/js/components/AppFooter.vue`
  - `resources/js/components/LoadingIndicator.vue`
  - `resources/js/components/AuthForm.vue`

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
- [ ] Create test data fixtures

## Implementation Tasks
1. **Common Components**:
   - [x] Create AppHeader (with auth state)
   - [x] Create AppFooter
   - [x] Implement LoadingIndicator

2. **State Management**:
   - [x] Implement Auth Store (7/7 tests passing)
   - [x] Implement Loading Store (4/4 tests passing)

3. **Services**:
   - [x] Configure API Service (Axios)
   - [x] Implement Auth Service (8/8 tests passing)

4. **Testing Infrastructure**:
   - [x] Configure test environment (Vitest + Vue Test Utils)
   - [x] Set up CI/CD test reporting (Vitest coverage)
   - [x] Write unit tests for all stores
   - [x] Write unit tests for all services
   - [x] Write unit tests for common components (including AuthForm)

## Test Fixes
- [x] Fixed Pinia initialization in auth service tests
- [x] Fixed AppHeader loading state test
- [x] Fixed route mocking in AppHeader tests
- [x] Achieved 100% coverage on stores
- [x] Achieved 85%+ coverage on services
- [x] All 34 tests passing (including 8 new AppHeader tests)
- [x] Fixed AuthForm email validation tests (19/19 passing)
- [x] Implemented proper server validation error display

## Acceptance Criteria
- All tests pass in isolation and together
- Core components render properly
- Auth flow works with mocked backend
- Loading states are managed properly
