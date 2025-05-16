# Sprint 5: Partner Dashboard (TDD Focus)

## References
- WBS: web-planning/wbs-restaurant-partner.md
- Planning: web-planning/restaurant-partner.md

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Partner login flow
  - [ ] Order management
- [ ] Write unit tests for:
  - [ ] PartnerDashboard component
  - [ ] OrderList component
  - [ ] OrderCard component

## Implementation Tasks
1. **Frontend Foundation**:
   - [ ] Convert all boilerplate pages to Vuetify3
   - [ ] Replace router-links with Inertia Link
   - [ ] Add data-test attributes for testing
   - [ ] Update tech documentation

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
