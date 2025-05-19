## Test Improvements (2025-05-15)

- Refactored partner login tests to:
  - Handle exact error message matching ("The email field is required. (and 1 more error)")
  - Properly validate Portuguese error messages ("As credenciais fornecidas estão incorretas")
  - Fix promise handling in test assertions
  - Add comprehensive logging for debugging
  - Fixed router reference in Login.vue
  - Added data-test attribute to Partner Dashboard
  - Verified successful login flow with API response checks
- Added error handling for Ziggy route errors in tests
- Updated test selectors to match current UI components

## CORS & API Configuration (2025-05-15)

- Added proxy configuration in vite.config.js to forward API requests to Laravel backend:
  - Configured proxies for /api, /register, and /login endpoints
  - Target set to http://127.0.0.1:8000 (Laravel server)
- Updated API service to use relative URLs in development:
  - Uses '/api/v1' in development to leverage Vite's proxy
  - Falls back to VITE_API_URL in production
- Enhanced auth store to handle custom API endpoints:
  - Added support for _endpoint parameter in register function
  - Improved error handling for API requests

## E2E Test File Creation (2025-05-16)

- Created initial placeholder E2E test files for various web areas.
- Added basic `describe` and `it` blocks outlining test concepts.
- Included emojis in `cy.log` for better terminal visibility.
- Covered the following areas:
    - Admin flow (node management, region management, delivery monitoring, system configuration)
    - Analytics/Reporting (no new files, existing reviewed)
    - Auth (added protected route test, existing reviewed)
    - Checkout (added successful order, confirmation, addons, and error handling tests, existing reviewed)
    - Common Components (added tests for auth forms/modals, address input, payment method input, data tables, modals/dialogs, existing reviewed)
    - Courier flow (no new files, existing reviewed)
    - Customer Ordering (no new files, existing reviewed)
    - Delivery/Hybrid Delivery (added hybrid specific tests, existing reviewed)
    - Home Page (no new files, existing reviewed)
    - Menu System (added tests for dynamic routing, quantity/addon selection, cart interactions, real-time pricing, auth integration, existing reviewed)
    - Notifications (no new files, existing reviewed)
    - Offline Mode (no new files, existing reviewed)
    - Orders/Order Tracking (no new files, existing reviewed)
    - Partner flow (no new files, existing reviewed)
    - Pricing/Community Pricing (added audio forum test, existing reviewed)
    - Profile Management (added tests for address and payment management, existing reviewed)
    - Support/Ticket System (no new files, existing reviewed)

## Centralized Logging and Cypress Test (2025-05-19)

### Log Store Implementation
- Created `resources/js/stores/log.js` with enhanced features:
  - Stores multiple log entries with timestamps
  - Provides Cypress access via window.logStore API
  - Includes clear() functionality
  - Limits log history to 100 entries
- Replaces console.log usage with log store for:
  - Better testability in Cypress
  - Structured logging with timestamps
  - Consistent logging across components

### Testing Infrastructure
- Created TestComponent (`resources/js/components/TestComponent.vue`) demonstrating log store usage
- Updated TestLog page (`resources/js/Pages/TestLog.vue`) to:
  - Display TestComponent
  - Show latest log and full history
- Enhanced Cypress test (`cypress/e2e/common/log-store.cy.js`) to:
  - Click button to trigger logging
  - Verify logs appear in UI
  - Check logs are available to Cypress
  - Output logs to terminal with emoji

### Usage Guidelines
1. Import log store: `import { useLogStore } from '@/stores/log'`
2. Use in components:
```javascript
const logStore = useLogStore();
logStore.log('Your message here');
```
3. In tests, access logs via:
```javascript
cy.window().then(win => {
  const logs = win.logStore.getLogs();
});
```
