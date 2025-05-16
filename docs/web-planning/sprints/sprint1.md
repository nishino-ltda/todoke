# Sprint 1: Foundation & Core Components (TDD Focus)

## References
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-stores-services.md
- Planning: web-planning/common-components.md
- Planning: web-planning/stores-services.md

## Testing Goals
- [ ] Setup Cypress for E2E testing (basic configuration and test file created)
- [ ] Write unit tests for:
  - [ ] Auth Store
  - [ ] API Service
  - [ ] AppHeader component
  - [ ] AppFooter component
- [ ] Create test data fixtures

## Implementation Tasks
1. **Common Components**:
   - [ ] Create AppHeader (with auth state)
   - [ ] Create AppFooter
   - [ ] Implement LoadingIndicator

2. **State Management**:
   - [ ] Implement Auth Store
   - [ ] Implement Loading Store

3. **Services**:
   - [ ] Configure API Service (Axios)
   - [ ] Implement Auth Service

4. **Testing Infrastructure**:
   - [ ] Configure test environment (Vitest + Vue Test Utils)
   - [ ] Set up CI/CD test reporting (Vitest coverage)
   - [ ] Write unit tests for all stores
   - [ ] Write unit tests for all services
   - [ ] Write unit tests for common components

## Test Fixes
- [ ] Fixed Pinia initialization in auth service tests
- [ ] Fixed AppHeader loading state test
- [ ] Achieved 100% coverage on stores
- [ ] Achieved 85%+ coverage on services
- [ ] All 34 tests passing

## Acceptance Criteria
- All tests pass in isolation and together
- Core components render properly
- Auth flow works with mocked backend
- Loading states are managed properly
