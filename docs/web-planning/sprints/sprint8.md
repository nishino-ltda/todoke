# Sprint 8: Notifications & Real-time (TDD Focus)

## References
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-stores-services.md
- Planning: web-planning/common-components.md

### Vue Components (already built)
- `resources/js/Components/AppAlert.vue` — success/error/info variants, auto-dismiss
- `resources/js/Components/AppHeader.vue` — notification bell in authenticated layout

### Stores (already built)
- `resources/js/stores/notification.js` — notification state management
- `resources/js/stores/__tests__/notification.spec.js` — 60+ tests, all passing

### E2E Test Files (placeholders)
- `cypress/e2e/notifications/notification-system.cy.js`

## Prerequisites (completed)
- [x] AppAlert component (success/error/info variants, auto-dismiss)
- [x] NotificationsStore with full test coverage (60+ tests)
- [x] Notification display logic in AppHeader
- [x] Notification dismissal functionality
- [x] Translation support (all notification text externalized)

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Notification display across roles (customer, courier, partner, admin)
  - [ ] Real-time updates (polling or WebSocket)
  - [ ] Notification dismissal
  - [ ] Error notification display
  - [ ] Translated notification content
- [ ] Add unit tests for:
  - [ ] WebSocket service
  - [ ] Real-time polling implementation

## Implementation Tasks
1. **Real-time Infrastructure**:
   - [ ] Implement WebSocket service (`resources/js/services/websocket.js`) or polling-based real-time updates
   - [ ] Connect to notification events
   - [ ] Implement update listeners for order status, delivery status
   - [ ] Add UI update triggers across dashboards
   - [ ] Ensure real-time updates respect user language preference

2. **Notification Integration**:
   - [ ] Wire notifications into courier dashboard (delivery accepted, status changes)
   - [ ] Wire notifications into partner dashboard (new orders, order status changes)
   - [ ] Wire notifications into customer dashboard (order confirmation, delivery updates)
   - [ ] Wire notifications into admin dashboard (node approval requests, system alerts)
   - [ ] Support translated notification content with dynamic values

3. **E2E Tests**:
   - [ ] Write actual test logic in `cypress/e2e/notifications/notification-system.cy.js`
   - [ ] Test notification display for each role
   - [ ] Test notification dismissal
   - [ ] Test with pt-BR locale
   - [ ] Test real-time updates (mock via cy.intercept)

## Acceptance Criteria
- Notifications display correctly in all supported languages for all roles
- Real-time updates work across components (polling or WebSocket)
- Notification history is maintained with translations
- Notification content is externalized to translation files
- E2E tests verify notification behavior for all user roles
