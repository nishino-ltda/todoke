# Sprint 2: Authentication, Home Page & Layout System (TDD Focus)

## References

### Components
- Authentication:
  - `resources/js/Components/AuthForm.vue`
- Home Page:
  - `resources/js/Components/HomeHero.vue`
  - `resources/js/Components/HomeFeatures.vue`
- Layouts:
  - (To be created in `resources/js/layouts/` or `resources/js/Components/`)

### Stores & Services
- Auth Store:
  - `resources/js/stores/auth.js` (or .ts)
- API Service:
  - `resources/js/services/api.js` (or .ts)

### Test Files
- Authentication:
  - `cypress/e2e/auth/login.cy.js`
  - `cypress/e2e/auth/registration.cy.js`
- Home Page:
  - `cypress/e2e/home/home-page.cy.js`

### Planning Docs
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-home.md
- Planning: web-planning/home.md
- Planning: web-planning/common-components.md

## Testing Goals
- [x] Write E2E tests for:
  - [x] Login flow
  - [x] Registration flow
  - [x] Language switching functionality
- [x] Write unit tests for:
  - [x] AuthForm component (including translation behavior)
  - [x] HomeHero component (including translation behavior)
  - [x] HomeFeatures component (including translation behavior)
  - [ ] GuestLayout component (including translation behavior)
  - [ ] AuthenticatedLayout component (including translation behavior)
  - [x] Translation utilities
  - [x] Language preference storage
- [x] Integration tests for:
  - [x] Component rendering with different locales
  - [x] Fallback language behavior

## Implementation Tasks
1. **Layout System**:
   - [ ] Create GuestLayout
   - [ ] Create AuthenticatedLayout
   - [ ] Apply Layouts to all pages
   - [ ] Implement responsive design
   - [ ] Add navigation integration
   - [ ] Add translation support to all layout text

2. **Authentication**:
   - [x] Implement AuthForm component with:
     - [x] Translation keys for all UI text
     - [x] Language-aware validation messages
     - [x] Error messages from translation files
   - [x] Connect to Auth Service
   - [x] Add form validation
   - [x] Implement error handling

3. **Home Page**:
   - [x] Create HomeHero component with translation support
   - [x] Create HomeFeatures component with translation support
   - [x] Implement responsive design
   - [x] Add navigation links with translated labels

4. **State Management**:
   - [ ] Extend Auth Store for registration
   - [ ] Add error state handling
   - [ ] Implement language preference storage

5. **Translation Infrastructure**:
   - [x] Set up Vue I18n integration with Portuguese (pt-BR) as default language
   - [x] Create comprehensive pt-BR translation files
   - [x] Implement language switching mechanism
   - [x] Add translation utilities to common components
   - [x] Ensure all UI text is externalized to translation files

## Acceptance Criteria
- All auth tests pass including translation tests
- Home page components render properly in all supported languages
- Form validation works with localized messages
- Error states are handled gracefully with translated messages
- Language switching works across all components
- Translation files are properly structured and complete
- Fallback to default language works when translations are missing
- All UI text is externalized to translation files
