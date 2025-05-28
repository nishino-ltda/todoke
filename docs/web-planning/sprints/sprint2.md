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
- [ ] Write E2E tests for:
  - [ ] Login flow
  - [ ] Registration flow
  - [ ] Language switching functionality
- [ ] Write unit tests for:
  - [ ] AuthForm component (including translation behavior)
  - [ ] HomeHero component (including translation behavior)
  - [ ] HomeFeatures component (including translation behavior)
  - [ ] GuestLayout component (including translation behavior)
  - [ ] AuthenticatedLayout component (including translation behavior)
  - [ ] Translation utilities
  - [ ] Language preference storage
- [ ] Integration tests for:
  - [ ] Component rendering with different locales
  - [ ] Fallback language behavior

## Implementation Tasks
1. **Layout System**:
   - [ ] Create GuestLayout
   - [ ] Create AuthenticatedLayout
   - [ ] Apply Layouts to all pages
   - [ ] Implement responsive design
   - [ ] Add navigation integration
   - [ ] Add translation support to all layout text

2. **Authentication**:
   - [ ] Implement AuthForm component with:
     - [ ] Translation keys for all UI text
     - [ ] Language-aware validation messages
     - [ ] Error messages from translation files
   - [ ] Connect to Auth Service
   - [ ] Add form validation
   - [ ] Implement error handling

3. **Home Page**:
   - [ ] Create HomeHero component with translation support
   - [ ] Create HomeFeatures component with translation support
   - [ ] Implement responsive design
   - [ ] Add navigation links with translated labels

4. **State Management**:
   - [ ] Extend Auth Store for registration
   - [ ] Add error state handling
   - [ ] Implement language preference storage

5. **Translation Infrastructure**:
   - [ ] Set up Vue I18n integration
   - [ ] Create pt-BR translation files
   - [ ] Implement language switching mechanism
   - [ ] Add translation utilities to common components

## Acceptance Criteria
- All auth tests pass including translation tests
- Home page components render properly in all supported languages
- Form validation works with localized messages
- Error states are handled gracefully with translated messages
- Language switching works across all components
- Translation files are properly structured and complete
- Fallback to default language works when translations are missing
- All UI text is externalized to translation files
