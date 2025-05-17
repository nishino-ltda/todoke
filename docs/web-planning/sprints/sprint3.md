# Sprint 3: Menu Browsing & Cart (TDD Focus)

## References
- WBS: web-planning/wbs-menu.md
- WBS: web-planning/wbs-common-components.md
- Planning: web-planning/menu.md
- Controllers:
  - Customer/MenuController.php
  - Customer/CheckoutController.php
- Vue Components:
  - Pages/Customer/Menu.vue
  - Pages/Customer/Checkout.vue
- Routes:
  - GET /customer/menu (MenuController@index)
  - GET /customer/checkout (CheckoutController@index)

## Testing Goals
- [ ] Unit tests for:
  - [ ] ProductList component
  - [ ] ProductCard component
  - [ ] Menu page component
  - [ ] CartIcon component
  
- [ ] E2E tests for:
  - [ ] Product browsing
  - [ ] Add to cart flow
  - [ ] Cart persistence

## Implementation Tasks
1. **Menu Components**:
   - [ ] Create ProductList component
   - [ ] Create ProductCard component
   - [ ] Implement product search/filter
   - [ ] Create ProductDetailsModal with addon selection

2. **Cart Functionality**:
   - [ ] Implement Cart Store
   - [ ] Create CartIcon component with badge count
   - [ ] Add advanced cart operations (add/remove/persistence)
   - [ ] Implement checkout flow

3. **State Management**:
   - [ ] Extend Cart Store for persistence (localStorage integration)
   - [ ] Add cart item count to AppHeader
   - [ ] Implement direct slug routing (e.g., /restaurant-name)

## Acceptance Criteria
- [ ] Users can browse products
- [ ] Products can be added/removed from cart
- [ ] Cart state persists between sessions
- [ ] Cart icon updates correctly
