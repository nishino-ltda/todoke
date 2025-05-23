# Sprint 6: Courier Dashboard (TDD Focus)

## References
- WBS: web-planning/wbs-courier.md
- Planning: web-planning/courier.md
- Web Routes: routes/web.php (Courier routes)
- API Routes: routes/api.php (Delivery endpoints)
- Controllers:
  - app/Http/Controllers/Courier/DashboardController.php
  - app/Http/Controllers/Courier/DeliveryController.php
  - app/Http/Controllers/Courier/HybridDeliveryController.php
  - app/Http/Controllers/API/DeliveryManagementController.php
  - app/Http/Controllers/API/DeliveryStatusController.php
- Vue Components (to be created):
  - resources/js/Pages/Courier/Dashboard.vue
  - resources/js/Components/Courier/AvailabilityToggle.vue
  - resources/js/Components/Courier/AvailableDeliveriesList.vue
  - resources/js/Components/Courier/AssignedDeliveryDetails.vue
  - resources/js/Components/Courier/StatusUpdateButtons.vue
  - resources/js/Components/Courier/DeliveryMap.vue
- E2E Tests (to be implemented):
  - cypress/e2e/courier/courier-dashboard.cy.js
  - cypress/e2e/courier/courier-flow.cy.js
  - cypress/e2e/courier/dashboard.cy.js

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Courier login flow
  - [ ] Delivery acceptance flow
  - [ ] Status updates
  - [ ] Language switching
- [ ] Write unit tests for:
  - [ ] CourierDashboard component
  - [ ] AvailableDeliveriesList component
  - [ ] DeliveryMap component
  - [ ] Translation functionality

## Implementation Tasks
1. **Internationalization**:
   - [ ] Implement translation file structure
   - [ ] Add pt-BR translations for all UI text
   - [ ] Create language switcher component
   - [ ] Configure API to respect Accept-Language header
   - [ ] Update database models for multilingual content

2. **Dashboard Components**:
   - [ ] Create CourierDashboard layout
   - [ ] Implement AvailableDeliveriesList
   - [ ] Create DeliveryMap component
   - [ ] Add status update buttons

2. **Delivery Management**:
   - [ ] Connect to Delivery API
   - [ ] Implement delivery acceptance
   - [ ] Add status update functionality
   - [ ] Implement geolocation tracking

3. **State Management**:
   - [ ] Implement Courier Store
   - [ ] Add real-time delivery updates

## Acceptance Criteria
- Couriers can view available deliveries
- Deliveries can be accepted/rejected
- Status updates work correctly
- Map shows current position and route
- All UI text is properly translated to pt-BR
- Language switcher works correctly
- API responses respect Accept-Language header
