# Sprint 3: Menu Browsing & Cart (TDD Focus)

## References
- WBS: web-planning/wbs-menu.md
- WBS: web-planning/wbs-common-components.md
- Planning: web-planning/menu.md

## Testing Goals
✅ Completed:
- Unit tests for:
  - ProductList component (all cases passing)
  - ProductCard component (all cases passing)
  - Menu page component (axios mocking and async handling implemented)
  - CartIcon component (all cases passing, badge display verified)
  
✅ Completed:
- E2E tests for:
  - Product browsing
  - Add to cart flow
  - Cart persistence

## Implementation Tasks
1. **Menu Components**:
   - [x] Create ProductList component
   - [x] Create ProductCard component
   - [ ] Implement product search/filter

2. **Cart Functionality**:
   - [x] Implement Cart Store
   - [x] Create CartIcon component
   - [x] Add basic cart operations (add/remove)

3. **State Management**:
   - [x] Extend Cart Store for persistence (localStorage integration)
   - [ ] Add cart item count to AppHeader
   - [x] Implement direct slug routing (e.g., /restaurant-name)

## Acceptance Criteria
✅ Users can browse products
✅ Products can be added/removed from cart
✅ Cart state persists between sessions
✅ Cart icon updates correctly
