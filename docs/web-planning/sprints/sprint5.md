# Sprint 5: Partner Dashboard (TDD Focus)

## References
### Planning Documents
- web-planning/wbs-restaurant-partner.md
- web-planning/restaurant-partner.md

### E2E Tests
- cypress/e2e/partner/partner-dashboard.cy.js
- cypress/e2e/partner/order-management.cy.js
- cypress/e2e/partner/partner-flow.cy.js

### Route Controllers
- app/Http/Controllers/Partner/DashboardController.php
- app/Http/Controllers/Partner/OrderController.php  
- app/Http/Controllers/Partner/ProductController.php
- app/Http/Controllers/Partner/AddonController.php
- app/Http/Controllers/Partner/RegionController.php
- app/Http/Controllers/Partner/NodeController.php
- app/Http/Controllers/Partner/SettingsController.php
- app/Http/Controllers/Partner/VariationController.php

### Vue Components
- resources/js/Pages/Partner/Dashboard.vue
- resources/js/Pages/Partner/Orders/Index.vue
- resources/js/Pages/Partner/Orders/Show.vue
- resources/js/Pages/Partner/Orders/BatchCreate.vue
- resources/js/Pages/Partner/Products/Index.vue
- resources/js/Pages/Partner/Products/Show.vue
- resources/js/Pages/Partner/Products/Create.vue
- resources/js/Pages/Partner/Products/Edit.vue
- resources/js/Pages/Partner/Products/Variations/Index.vue
- resources/js/Pages/Partner/Products/Variations/Create.vue
- resources/js/Pages/Partner/Products/Variations/Edit.vue
- resources/js/Pages/Partner/Addons/Index.vue
- resources/js/Pages/Partner/Addons/Show.vue
- resources/js/Pages/Partner/Addons/Create.vue
- resources/js/Pages/Partner/Addons/Edit.vue
- resources/js/Pages/Partner/Regions/Index.vue
- resources/js/Pages/Partner/Regions/Show.vue
- resources/js/Pages/Partner/Regions/Create.vue
- resources/js/Pages/Partner/Regions/Edit.vue
- resources/js/Pages/Partner/Nodes/Index.vue
- resources/js/Pages/Partner/Nodes/Show.vue
- resources/js/Pages/Partner/Nodes/Create.vue
- resources/js/Pages/Partner/Nodes/Edit.vue
- resources/js/Pages/Partner/Settings/Index.vue

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
