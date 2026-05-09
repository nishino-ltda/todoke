# Work Breakdown Structure: End-to-End Testing

## 1. Test Setup
- [x] Install and configure Cypress (in package.json: `npm run test:e2e`, `test:e2e:ci`, `test:e2e:local`)
- [x] Set up test environment (commands.js with custom auth/state commands)
- [x] Create test data fixtures

## 2. Authentication Tests
- [x] Multi-role login/logout (auth_flow.cy.js — customer, partner, courier, admin)
- [x] Registration with validation errors (auth_flow.cy.js, registration.cy.js)
- [x] Invalid credential handling
- [x] Password reset flow (password-reset.cy.js)

## 3. Customer Flow Tests
- [x] Menu browsing (customer_flow.cy.js, menu-system.cy.js)
- [x] Cart operations (customer_flow.cy.js, checkout-flow.cy.js)
- [x] Checkout process (customer_flow.cy.js)
- [x] Order placement (ordering.cy.js, order-tracking.cy.js)

## 4. Partner Flow Tests
- [x] Dashboard metrics (partner_flow.cy.js, partner-dashboard.cy.js, dashboard.cy.js)
- [x] Order management (partner_flow.cy.js, order-management.cy.js)
- [x] Product management (partner_flow.cy.js)
- [x] (Logistics) Region/node management (region-management.cy.js)

## 5. Courier Flow Tests
- [x] Availability toggle (courier_flow.cy.js, courier-dashboard.cy.js, dashboard.cy.js)
- [x] Delivery acceptance (courier_flow.cy.js)
- [x] Status updates (courier_flow.cy.js)
- [x] Map integration (courier_flow.cy.js — DeliveryMap)

## 6. Admin Flow Tests
- [x] User management (admin_flow.cy.js, user-management.cy.js)
- [x] Node approval (admin_flow.cy.js, node-management.cy.js)
- [x] System monitoring (admin_flow.cy.js, delivery-monitoring.cy.js)
- [x] Configuration changes (admin_flow.cy.js, system-configuration.cy.js)
- [x] Region management (region-management.cy.js)

## 7. Support Flow Tests
- [x] Ticket submission (support_flow.cy.js, ticket-system.cy.js)
- [x] Ticket viewing (support_flow.cy.js, tickets.cy.js)
- [x] Ticket reply flow (support_flow.cy.js)

## 8. Translation Testing
- [x] Verify UI text renders in correct language (default: pt-BR) (i18n_flow.cy.js)
- [x] Test language switching functionality (i18n_flow.cy.js)
- [x] Verify fallback to English when translation missing (i18n_flow.cy.js)
- [x] Test notifications with dynamic values in translations
- [x] Check date/number formatting per locale (i18n_flow.cy.js)
- [x] Verify all tests use i18n-aware selectors (data-cy standard)
- [x] Test with multiple language configurations (i18n_flow.cy.js)
- [x] Validate translation key consistency across tests

## 9. CI/CD Integration
- [ ] Add to build pipeline
- [ ] Configure test reporting
- [ ] Set up test artifacts
