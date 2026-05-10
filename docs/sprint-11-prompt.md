# Sprint 11 — Prompt for Implementation

## Context
You are working on the TODOKE delivery platform MVP. The core features are complete. This sprint focuses on tightening e2e test coverage of critical flows and cleaning up dead test stubs.

## Key Background

### Seeded Test Credentials (all password: `password123`)
- **Admin**: admin@todoke.test
- **Partner**: partner@todoke.test
- **Courier**: courier@todoke.test
- **Customer**: customer@todoke.test
- **Locked User**: locked@todoke.test

### WBS Location
All WBS files are in `docs/web-planning/*.md`. Review the unchecked items in each WBS before implementing.

## Tasks

### 1. Delete Dead Test Stubs (3 files)
These files contain only `cy.fail('Test not implemented')` calls. Their flows are already covered by real tests in the same directory:
- `cypress/e2e/partner/partner-flow.cy.js` — covered by `partner_flow.cy.js`
- `cypress/e2e/partner/dashboard.cy.js` — covered by `partner-dashboard.cy.js`
- `cypress/e2e/partner/region-management.cy.js` — covered by `partner_flow.cy.js` region section

Delete these 3 files.

### 2. Implement Checkout E2E Tests
`cypress/e2e/checkout/checkout-flow.cy.js` contains 8 stubs. Implement real tests for:
- Add items to cart, verify quantity/price
- Address selection / new address input
- Payment method selection and card validation
- Successful order placement (API intercept with mocked response)
- Order confirmation display
- Addons reflected in cart total
- Error handling (validation errors, API failure)
- Mobile responsiveness (viewport test)

Use `cy.intercept` to mock API calls. Login as `customer@todoke.test`.

### 3. Implement Home Page E2E Tests
`docs/web-planning/wbs-home.md` has unchecked items for E2E tests. `cypress/e2e/home/home.cy.js` exists with 3 basic tests. Expand to cover:
- Hero section renders with correct content
- Register/Login CTA buttons navigate correctly
- Features section renders all 3 feature cards
- CTA section shows 3 user-type buttons
- Smooth scrolling works (click nav links)
- Language switching persists on home page
- Responsive layout at mobile viewport

### 4. Admin Panel — Charts and Filters
`docs/web-planning/wbs-admin.md` has unchecked:
- "Implement charts/graphs for data visualization"
- "Add time period filters"

Check the existing `AdminStatsWidget.vue` to see what already exists. If charts are missing, add them (Chart.js or similar — check if already used). Add time-period filter buttons (7d, 30d, 90d) connected to `data-cy` attributes so the existing Cypress tests (`admin_flow.cy.js` checks for these) pass.

### 5. Admin Panel — Delivery Detail View
`docs/web-planning/wbs-admin.md` has unchecked "Add detailed delivery view page".

Create a delivery detail page at `/admin/deliveries/{id}` with:
- Full delivery info (customer, courier, origin, destination, status timeline)
- Back button to `/admin/deliveries`
- `data-cy` attributes for testability

### 6. Remove/Replace `cy.fail` in Remaining Stubs (lower priority)
The following files also contain `cy.fail` stubs but are **not a priority** (skip if time is limited):
- `cypress/e2e/offline/offline-mode.cy.js`
- `cypress/e2e/delivery/hybrid-delivery.cy.js` (drone flows — explicitly NOT a priority)

## Constraints
- Do NOT modify `database/seeders/` or `database/factories/`
- Do NOT modify existing working Cypress tests in `partner_flow.cy.js`, `order-management.cy.js`, `partner-dashboard.cy.js`
- All new tests must use `data-cy` selectors for resilience
- Use `cy.intercept` for API mocking, not real API calls
- Follow the existing patterns (login in `beforeEach`, `cy.log` steps)
- Verify WBS checklists in `docs/web-planning/` after completing each task
