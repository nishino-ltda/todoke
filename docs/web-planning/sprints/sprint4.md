# Sprint 4: Checkout Flow & Internationalization (TDD Focus)

## Completed Tasks
- [ ] Registration flow tests:
  - [ ] Client-side validation
  - [ ] API error handling
  - [ ] All 8 test cases passing

## References
- WBS: web-planning/wbs-common-components.md
- WBS: web-planning/wbs-stores-services.md
- Planning: web-planning/common-components.md
- Planning: web-planning/stores-services.md
- Controller: app/Http/Controllers/Customer/CheckoutController.php
- Controller: app/Http/Controllers/Customer/OrderController.php
- Controller: app/Http/Controllers/Partner/OrderController.php
- Vue Page: resources/js/Pages/Customer/Checkout.vue
- E2E Tests: cypress/e2e/checkout/checkout-flow.cy.js

## Testing Goals
- [ ] Write E2E tests for:
  - [ ] Checkout process
  - [ ] Order submission
  - [ ] Addons handling
  - [ ] Error cases
  - [ ] Translation functionality
- [ ] Write unit tests for:
  - [ ] CheckoutForm component
  - [ ] AddressInput component
  - [ ] PaymentMethodInput component
  - [ ] Checkout page
  - [ ] Translation Store/Service

## Implementation Tasks
1. **Checkout Components**:
   - [ ] Create CheckoutForm component
   - [ ] Implement AddressInput
   - [ ] Implement PaymentMethodInput
   - [ ] Add form validation
   - [ ] Externalize all UI strings to translation files

2. **Order Processing**:
   - [ ] Connect to Order API
   - [ ] Handle order submission
   - [ ] Implement order confirmation
   - [ ] Support localized order confirmation messages

3. **State Management**:
   - [ ] Clear cart after successful order
   - [ ] Add order history to User Store
   - [ ] Implement Translation Store
   - [ ] Implement Translation Service

4. **Internationalization**:
   - [ ] Create pt-BR translation files
   - [ ] Implement language selector component
   - [ ] Add Accept-Language header to API requests
   - [ ] Update components to use translations
   - [ ] Add language preference to Auth Store

## Acceptance Criteria
- Users can complete checkout in supported languages
- Form validation works with localized messages
- Orders are submitted successfully with language context
- Cart clears after order
- Translation infrastructure is in place for future languages
