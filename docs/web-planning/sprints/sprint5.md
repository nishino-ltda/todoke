# Sprint 5: Partner Dashboard (TDD Focus)

## References
- WBS: web-planning/wbs-restaurant-partner.md
- Planning: web-planning/restaurant-partner.md

## Testing Goals
- [x] Write E2E tests for:
  - [x] Partner login flow (partner-login.cy.js) - Completed 2025-05-13
  - [x] Order management (partner-orders.cy.js) - Updated 2025-05-13
- [x] Write unit tests for:
  - [x] PartnerDashboard component (PartnerDashboard.spec.js)
  - [x] OrderList component (OrderList.spec.js)
  - [x] OrderCard component (OrderCard.spec.js)

## Implementation Tasks
1. **Frontend Foundation**:
   - [x] Converted all boilerplate pages to Vuetify3
   - [x] Replaced router-links with Inertia Link
   - [x] Added data-test attributes for testing
   - [x] Updated tech documentation

2. **Dashboard Components**:
   - [ ] Create PartnerDashboard layout
   - [ ] Implement OrderList component
   - [ ] Create OrderCard component
   - [ ] Add order status filtering

2. **Order Management**:
   - [ ] Connect to Order API
   - [ ] Implement order status updates
   - [ ] Add order preparation time tracking

3. **State Management**:
   - [ ] Implement Partner Store
   - [ ] Add real-time order updates

## Acceptance Criteria
- Partners can view their orders
- Order status can be updated
- Dashboard updates in real-time
- Filtering works correctly
