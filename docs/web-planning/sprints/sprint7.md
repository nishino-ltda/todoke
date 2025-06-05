# Sprint 7: Admin Panel (TDD Focus)

## References
- WBS: web-planning/wbs-admin.md
- Planning: web-planning/admin.md
- Translation Files: resources/lang/
- Controllers:
  - Admin/DashboardController.php
  - Admin/UserController.php
  - Admin/NodeController.php
- Vue Components:
  - Pages/Admin/Dashboard.vue
  - Pages/Admin/Users/Index.vue
  - Pages/Admin/Nodes/Index.vue
- E2E Tests:
  - admin-login.cy.js
  - user-management.cy.js
  - node-management.cy.js

## Internationalization Requirements
1. **Default Language**:
   - [ ] Set Portuguese (pt-BR) as default language for admin panel
   - [ ] Create comprehensive pt-BR translations for all admin components

2. **Translation Infrastructure**:
   - [ ] Implement language switching in admin UI (default pt-BR)
   - [ ] Externalize all admin UI text to translation files
   - [ ] Create scalable translation file structure
   - [ ] Configure API to respect Accept-Language header (default pt-BR)

3. **Database Considerations**:
   - [ ] Ensure admin-related database content supports multilingual data
   - [ ] Implement fallback to pt-BR when translations are missing

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Admin login flow
  - [ ] User management
  - [ ] Node approval
- [ ] Write unit tests for:
  - [ ] AdminDashboard component
  - [ ] UserList component
  - [ ] NodeList component

## Implementation Tasks
1. **Dashboard Components**:
   - [ ] Create AdminDashboard layout
   - [ ] Implement UserList component
   - [ ] Create NodeList component
   - [ ] Add search/filter functionality

2. **System Management**:
   - [ ] Connect to Admin API
   - [ ] Implement user management
   - [ ] Add node approval functionality
   - [ ] Create system stats widgets

3. **State Management**:
   - [ ] Implement Admin Store
   - [ ] Add real-time system updates

## Acceptance Criteria
- Admin panel fully functional in pt-BR by default
- Language switching works correctly
- All UI text is properly externalized to translation files
- API responses respect Accept-Language header
- Admins can manage users with localized interfaces
- Nodes can be approved/rejected with translated messages
- System stats display correctly in selected language
- Search/filter works as expected across languages
