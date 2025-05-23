# Sprint 8: Notifications & Real-time (TDD Focus)

## References
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-e2e-testing.md
- Planning: web-planning/common-components.md
- Planning: web-planning/e2e-testing.md
- Tests: cypress/e2e/notifications/notification-system.cy.js

### Relevant Controllers
- Customer/DashboardController
- Partner/DashboardController
- Courier/DashboardController
- Admin/DashboardController
- Customer/OrderController
- Partner/OrderController
- Courier/DeliveryController
- SupportController

### Vue Components
- resources/js/Components/common/AppAlert.vue
- resources/js/Components/common/AppHeader.vue
- resources/js/Stores/NotificationsStore.js (to be created)
- resources/js/Services/WebSocketService.js (to be created)

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Notification display
  - [ ] Real-time updates
- [ ] Write unit tests for:
  - [ ] NotificationsStore
  - [ ] AppAlert component
  - [ ] WebSocket service

## Implementation Tasks
1. **Notification System**:
   - [ ] Implement NotificationsStore
   - [ ] Create AppAlert component
   - [ ] Add notification display logic
   - [ ] Implement notification dismissal
   - [ ] Support translated notification content:
     - [ ] Store notifications with translation keys
     - [ ] Render notifications using Vue I18n
     - [ ] Include dynamic values in translations

2. **Real-time Updates**:
   - [ ] Set up WebSocket service
   - [ ] Connect to real-time API
   - [ ] Implement update listeners
   - [ ] Add UI update triggers
   - [ ] Ensure real-time updates respect user language preference

3. **State Management**:
   - [ ] Extend stores for real-time updates
   - [ ] Add notification history
   - [ ] Store notification translations in history

## Acceptance Criteria
- Notifications display correctly in all supported languages
- Real-time updates work across components and respect language preference
- WebSocket connection is stable
- Notification history is maintained with translations
- Notification content is externalized to translation files
