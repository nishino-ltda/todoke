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
  
✅ Completed:
- E2E tests for:
  - Product browsing
  - Add to cart flow
  - Cart persistence

## Implementation Tasks
1. **Menu Components**:
   - [ ] Create ProductList component
   - [ ] Create ProductCard component
   - [ ] Implement product search/filter

2. **Cart Functionality**:
   - [ ] Implement Cart Store
   - [ ] Create CartIcon component
   - [ ] Add basic cart operations (add/remove)

3. **State Management**:
   - [x] Extend Cart Store for persistence (localStorage integration)
   - [ ] Add cart item count to AppHeader
   - [x] Implement direct slug routing (e.g., /restaurant-name)

## Acceptance Criteria
✅ Users can browse products
✅ Products can be added/removed from cart
✅ Cart state persists between sessions
✅ Cart icon updates correctly
