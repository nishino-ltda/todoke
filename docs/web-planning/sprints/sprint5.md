# Sprint 5: Menu System (TDD Focus) — Completed 2026-05-09

## References
- WBS: web-planning/wbs-menu.md
- Planning: web-planning/menu.md
- Translation Files: resources/lang/en.json, resources/lang/pt-BR.json
- Controllers:
  - `app/Http/Controllers/MenuController.php` — public menu rendering (by identifier)
  - `app/Http/Controllers/API/PartnerController.php` — partner API endpoint
  - `app/Http/Controllers/Customer/CheckoutController.php` — checkout page
  - `app/Http/Controllers/Customer/OrderController.php` — order confirmation
- Vue Components (new/updated):
  - `resources/js/Pages/Customer/Menu.vue` — menu page with GuestLayout, loading/error states
  - `resources/js/Pages/Customer/Checkout.vue` — auth check, order summary, submission
  - `resources/js/Components/Cart.vue` — full cart component with +/-/remove/totals
  - `resources/js/Components/CartIcon.vue` — fixed checkout URL
  - `resources/js/Components/CheckoutForm.vue` — emits form data to parent
- Services/Stores:
  - `resources/js/services/order.js` — submitOrder with proper headers
  - `resources/js/stores/cart.js` — incrementQuantity, decrementQuantity, computed totals
- E2E Tests:
  - `cypress/e2e/customer/customer_flow.cy.js` — browsing, cart, checkout, language switching
  - `cypress/e2e/customer/ordering.cy.js` — full ordering flow with auth scenarios

## Routes Registered
- `GET /menu/{partnerSlug}` — `MenuController@show` (public, renders menu page)
- `GET /{partnerSlug}` — `MenuController@show` (direct partner link)
- `GET /api/v1/partners/{slug}` — `API\PartnerController@show` (returns partner + products + addons)

## Testing Goals
- [x] Write E2E tests for ordering flow (browsing, cart, checkout, language switching)
- [x] Verify all UI text is externalized to translation files
- [x] Test fallback behavior when translations are missing
- [x] Validate cart persistence across pages

## Implementation Tasks
1. **Dual Routing for Menu Page**:
   - [x] Traditional `/menu/{partnerSlug}` route
   - [x] Direct `/{partnerSlug}` route support
   - [x] Error handling for invalid slugs (redirect to home with translated message)

2. **Cart Component**:
   - [x] Cart items list with product name, addons, quantity, unit price, subtotal
   - [x] Quantity adjustment (+/-) buttons per item
   - [x] Remove item button
   - [x] Subtotal, delivery fee, total display
   - [x] "Proceed to Checkout" button
   - [x] Empty cart state with friendly message
   - [x] Full i18n support, data-test attributes

3. **Menu Page API Integration**:
   - [x] Accepts Inertia props (partner, products) from server
   - [x] Fallback API fetch via GET /api/v1/partners/{slug}
   - [x] Loading/error states with retry

4. **Auth Integration in Checkout**:
   - [x] Checks auth state on mount
   - [x] Login/register prompt if not authenticated
   - [x] Redirect back to checkout after login (cart preserved)

5. **Checkout Data Flow**:
   - [x] Order submission via POST /api/v1/orders
   - [x] Payload formatted for API OrderController
   - [x] On success: clears cart, redirects to order confirmation
   - [x] Error handling with translated messages

6. **Cart Store Enhancements**:
   - [x] incrementQuantity(index), decrementQuantity(index)
   - [x] subtotal, deliveryFee, totalWithDelivery computed properties

7. **Translation Keys Added**:
   - [x] Cart: empty_title, empty_message, unit_price, subtotal, delivery_fee, proceed_to_checkout
   - [x] Menu: loading, error_title, retry, not_found, partner_type
   - [x] Checkout: login_required_title, login_required_message, empty_cart_title, empty_cart_message, browse_menu, order_summary

## Acceptance Criteria
- [x] Users can browse products from a partner's menu
- [x] Products can be added to cart with addon selection
- [x] Cart displays items with correct quantities and totals
- [x] Users can proceed to checkout (with auth prompt if needed)
- [x] Orders are submitted successfully with locale context
- [x] All UI text is externalized to translation files (pt-BR default)
- [x] E2E tests verify the full ordering flow

## Minor Refinements (deferred)
- [ ] Implement partner-specific layout with branding header
- [ ] Show product variations with translated labels
- [ ] Add image gallery/carousel with translated captions
