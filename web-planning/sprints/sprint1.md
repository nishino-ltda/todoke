# Sprint 1: Foundation & Core Components (TDD Focus)

## References
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-stores-services.md
- Planning: web-planning/common-components.md
- Planning: web-planning/stores-services.md

## Testing Goals
- [x] Setup Cypress for E2E testing (basic configuration and test file created)
- [x] Write unit tests for:
  - [x] Auth Store
  - [x] API Service
  - [x] AppHeader component
  - [x] AppFooter component
- [ ] Create test data fixtures

## Implementation Tasks
1. **Common Components**:
   - [x] Create AppHeader (with auth state)
   - [x] Create AppFooter
   - [x] Implement LoadingIndicator

2. **State Management**:
   - [x] Implement Auth Store
   - [x] Implement Loading Store

3. **Services**:
   - [x] Configure API Service (Axios)
   - [x] Implement Auth Service

4. **Testing Infrastructure**:
   - [x] Configure test environment (Vitest + Vue Test Utils)
   - [x] Set up CI/CD test reporting (Vitest coverage)
   - [x] Write unit tests for all stores
   - [x] Write unit tests for all services
   - [x] Write unit tests for common components

## Test Fixes
- [x] Fixed Pinia initialization in auth service tests
- [x] Fixed AppHeader loading state test
- [x] Achieved 100% coverage on stores
- [x] Achieved 85%+ coverage on services
- [x] All 34 tests passing

## Acceptance Criteria
- All tests pass in isolation and together
- Core components render properly
- Auth flow works with mocked backend
- Loading states are managed properly
