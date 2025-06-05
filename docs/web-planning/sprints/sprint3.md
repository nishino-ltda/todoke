# Sprint 3: Menu Browsing & Cart with Internationalization (TDD Focus)

## References
- WBS: web-planning/wbs-menu.md
- WBS: web-planning/wbs-common-components.md
- Planning: web-planning/menu.md
- Translation Patterns: memory-bank/systemPatterns.md
- Controllers:
  - Customer/MenuController.php
  - Customer/CheckoutController.php
- Vue Components:
  - Pages/Customer/Menu.vue
  - Pages/Customer/Checkout.vue
- Routes:
  - GET /customer/menu (MenuController@index)
  - GET /customer/checkout (CheckoutController@index)
- E2E Tests:
  - cypress/e2e/menu/menu-system.cy.js
  - cypress/e2e/checkout/checkout-flow.cy.js

## Testing Goals
- [ ] Unit tests for:
  - [ ] ProductList component (including translation tests)
  - [ ] ProductCard component (including translation tests)
  - [ ] Menu page component (including translation tests)
  - [ ] CartIcon component (including translation tests)
  
- [ ] E2E tests for:
  - [ ] Product browsing (with locale switching)
  - [ ] Add to cart flow (with locale switching)
  - [ ] Cart persistence (with locale switching)
  - [ ] Language switcher functionality

## Implementation Tasks
1. **Internationalization**:
   - [ ] Set up Vue I18n with Portuguese (pt-BR) as default language
   - [ ] Create comprehensive pt-BR translations for all menu/cart components
   - [ ] Create language switcher component
   - [ ] Externalize all UI strings to translation files
   - [ ] Implement locale persistence in user preferences
   - [ ] Add browser language detection with pt-BR fallback

2. **Menu Components**:
   - [ ] Create ProductList component (with translation support)
   - [ ] Create ProductCard component (with translation support)
   - [ ] Implement product search/filter (with translation support)
   - [ ] Create ProductDetailsModal with addon selection (with translation support)

3. **Cart Functionality**:
   - [ ] Implement Cart Store (with translation support)
   - [ ] Create CartIcon component with badge count (with translation support)
   - [ ] Add advanced cart operations (add/remove/persistence)
   - [ ] Implement checkout flow (with translation support)

4. **State Management**:
   - [ ] Extend Cart Store for persistence (localStorage integration)
   - [ ] Add cart item count to AppHeader
   - [ ] Implement direct slug routing (e.g., /restaurant-name)

## Acceptance Criteria
- [ ] Users can browse products in their preferred language
- [ ] Products can be added/removed from cart with translated messages
- [ ] Cart state persists between sessions including language preference
- [ ] Cart icon updates correctly
- [ ] Language switcher works across all components
- [ ] All UI text is externalized to translation files
- [ ] pt-BR translations available for all menu/cart components
