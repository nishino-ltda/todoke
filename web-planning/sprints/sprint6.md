# Sprint 6: Courier Dashboard (TDD Focus)

## References
- WBS: web-planning/wbs-courier.md
- Planning: web-planning/courier.md

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Courier login flow
  - [ ] Delivery acceptance flow
  - [ ] Status updates
- [ ] Write unit tests for:
  - [ ] CourierDashboard component
  - [ ] AvailableDeliveriesList component
  - [ ] DeliveryMap component

## Implementation Tasks
1. **Dashboard Components**:
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
