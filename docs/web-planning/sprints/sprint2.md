# Sprint 2: Authentication, Home Page & Layout System (TDD Focus)

## References

### Components
- Authentication:
  - `resources/js/components/AuthForm.vue`
- Home Page:
  - `resources/js/components/HomeHero.vue`
  - `resources/js/components/HomeFeatures.vue`
- Layouts:
  - (To be created in `resources/js/layouts/` or `resources/js/components/`)

### Stores & Services
- Auth Store:
  - `resources/js/stores/auth.js` (or .ts)
- API Service:
  - `resources/js/services/api.js` (or .ts)

### Planning Docs
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-home.md
- Planning: web-planning/home.md
- Planning: web-planning/common-components.md

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Login flow
  - [ ] Registration flow
- [ ] Write unit tests for:
  - [ ] AuthForm component
  - [ ] HomeHero component
  - [ ] HomeFeatures component
  - [ ] GuestLayout component
  - [ ] AuthenticatedLayout component

## Implementation Tasks
1. **Layout System**:
   - [ ] Create GuestLayout
   - [ ] Create AuthenticatedLayout
   - [ ] Apply Layouts to all pages
   - [ ] Implement responsive design
   - [ ] Add navigation integration

2. **Authentication**:
   - [ ] Implement AuthForm component
   - [ ] Connect to Auth Service
   - [ ] Add form validation
   - [ ] Implement error handling

3. **Home Page**:
   - [ ] Create HomeHero component
   - [ ] Create HomeFeatures component
   - [ ] Implement responsive design
   - [ ] Add navigation links

4. **State Management**:
   - [ ] Extend Auth Store for registration
   - [ ] Add error state handling

## Acceptance Criteria
- All auth tests pass
- Home page components render properly
- Form validation works
- Error states are handled gracefully
