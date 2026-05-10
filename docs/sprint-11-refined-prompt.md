# Sprint 11 Refined — Critical E2E & Admin Enhancements

## Context
You are finalizing the TODOKE MVP. Core features are solid, but E2E test coverage for the most critical customer journey (Checkout) is missing, and the Admin Panel needs a dedicated detail view for deliveries.

## Seeded Test Credentials (password: `password123`)
- **Admin**: `admin@todoke.test`
- **Support**: `support@todoke.test` (handles tickets)
- **Partner**: `partner@todoke.test`
- **Courier**: `courier@todoke.test`
- **Customer**: `customer@todoke.test`

## Tasks

### 1. Test Suite Cleanup (Delete Redundant/Dead Files)
Delete these files which are either empty stubs or duplicates covered by more detailed tests:
- `cypress/e2e/partner/partner-flow.cy.js` (Note: `partner_flow.cy.js` with underscore is the good one)
- `cypress/e2e/partner/dashboard.cy.js` (Covered by `partner-dashboard.cy.js`)
- `cypress/e2e/partner/region-management.cy.js` (Covered by `partner_flow.cy.js`)

### 2. Implement Checkout E2E Tests (`cypress/e2e/checkout/checkout-flow.cy.js`)
Transform all `cy.fail` stubs into real tests. You MUST use `cy.intercept` for all API calls.
- **Cart Operations**: Add items, verify totals, quantity updates.
- **Address Selection**: Mock saved addresses and test "Add New Address" form.
- **Payment Flow**: Verify credit card form validation and successful processing mock.
- **Order Submission**: Full flow from Cart -> Checkout -> Success -> Confirmation Page.
- **Addons**: Ensure product addons (extra cheese, etc.) are correctly calculated in the total.
- **Error Handling**: Test API failures (500) and validation errors (422) during checkout.

### 3. Implement Home Page E2E Tests (`cypress/e2e/home/home.cy.js`)
- Update existing tests to use `data-cy` instead of `data-test`.
- Add tests for **Language Switching**: Change language and verify Hero text updates.
- Add tests for **Responsive Layout**: Verify mobile menu/scrolling.
- Verify all 3 CTA buttons (Customer, Partner, Courier) lead to correct registration paths.

### 4. Admin Panel — Dedicated Delivery Detail Page
Currently, delivery details are in a modal. Implement a dedicated page for deeper management.
- **New Page**: `resources/js/Pages/Admin/Deliveries/Show.vue`
- **Route**: `/admin/deliveries/{id}`
- **Features**: 
  - Status timeline, customer/courier info, pickup/dropoff map (using `DeliveryMap.vue`).
  - Link to this page from the "View" button in `Admin/Deliveries/Index.vue` (replace or augment the modal).
- **Update WBS**: Mark "Add detailed delivery view page" as complete in `docs/web-planning/wbs-admin.md`.

### 5. Notification System Verification
- In `cypress/e2e/notifications/notification-system.cy.js`, implement a test that simulates an incoming Reverb notification (e.g., `OrderUpdated`) and verifies the `NotificationCenter` shows it.

## Constraints
- **Drone flows are NOT a priority**. Skip `cypress/e2e/delivery/hybrid-delivery.cy.js`.
- Use `data-cy` for all selectors.
- Maintain i18n: all new UI text must be in `pt-BR.json` and `en.json`.
- Do NOT modify the core business logic of the backend unless necessary for the detail page.

## Success Criteria
- `npm run test:e2e:local` passes for Checkout, Home, and Admin.
- No `cy.fail` remaining in critical flow files.
- README.md correctly lists all test accounts.
