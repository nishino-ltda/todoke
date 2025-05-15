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
