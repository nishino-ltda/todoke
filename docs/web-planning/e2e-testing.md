# Web Area Planning: End-to-End Testing

## Purpose
Define the strategy for implementing end-to-end (E2E) tests to ensure the web application functions correctly from the user's perspective, interacting with the backend API.

## Framework
- **Cypress:** Recommended E2E testing framework due to its developer-friendly nature, strong documentation, and features like automatic waiting and real-time reloads.

## Key Test Scenarios
- **User Authentication:**
    - Successful customer login and logout.
    - Successful partner login and logout.
    - Successful courier login and logout.
    - Successful admin login and logout.
    - Attempting to access protected routes without authentication.
    - Handling incorrect login credentials.
- **Customer Flow (Menu & Ordering):**
    - Navigating to a partner's menu.
    - Browsing products and viewing details.
    - Adding products with variations/addons to the cart.
    - Updating and removing items from the cart.
    - Completing the checkout process (entering address, selecting payment).
    - Placing an order successfully.
    - Viewing order confirmation.
- **Partner Flow (Restaurant/Logistics):**
    - Logging in as a partner.
    - Viewing the dashboard with relevant metrics.
    - Viewing and updating order statuses.
    - Managing products and addons (add, edit, delete).
    - (For Logistics Partners) Managing regions and nodes (add, edit).
- **Courier Flow:**
    - Logging in as a courier.
    - Toggling availability status.
    - Viewing available delivery requests.
    - Accepting/rejecting a delivery.
    - Updating delivery statuses during the delivery process.
- **Admin Flow:**
    - Logging in as an admin.
    - Accessing all admin sections.
    - Managing users (view, activate/deactivate).
    - Approving/rejecting pending nodes.
    - Viewing system statistics.
- **Support Flow:**
    - Submitting a support ticket as different user types.
    - Viewing submitted tickets.

## Implementation Considerations
- Install and configure Cypress in the project.
- Organize Cypress tests by feature area (e.g., `cypress/integration/auth/`, `cypress/integration/menu/`).
- Use Cypress commands to interact with the UI elements (e.g., `cy.visit()`, `cy.get()`, `cy.type()`, `cy.click()`).
- Utilize Cypress assertions to verify expected outcomes (e.g., `should('contain', 'text')`, `should('be.visible')`, `should('have.class', 'active')`).
- Consider using Cypress fixtures for test data.
- Explore using `cy.intercept()` to mock API responses for testing specific frontend behavior in isolation or simulating error conditions.
- Integrate E2E tests into the CI/CD pipeline.
- **Translation Testing**:
  - Test UI text in all supported languages using `cy.contains()` with expected translations
  - Verify language switching functionality
  - Test fallback behavior when translations are missing
  - Check localized formatting of dates, numbers and currencies
  - Test notifications with dynamic values in translations

## Potential Files/Directories
- `cypress/` (Cypress test runner directory)
- `cypress/integration/` (Test files organized by feature)
- `cypress/fixtures/` (Test data)
- `cypress/support/` (Custom commands and support files)
- `cypress.json` (Cypress configuration)
