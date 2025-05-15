## Test Fixes (2025-05-13)

- Updated partner login tests to:
  - Use correct test credentials (partner@example.com/password123)
  - Expect Portuguese error messages from API
  - Follow actual login flow with proper redirects
- Modified auth store to redirect partners to /partner after login
- Aligned test assertions with actual API responses

## CORS Issue Fix (2025-05-15)

- Added proxy configuration in vite.config.js to forward API requests to Laravel backend:
  - Configured proxies for /api, /register, and /login endpoints
  - Target set to http://127.0.0.1:8000 (Laravel server)
- Updated API service to use relative URLs in development:
  - Uses '/api/v1' in development to leverage Vite's proxy
  - Falls back to VITE_API_URL in production
- Enhanced auth store to handle custom API endpoints:
  - Added support for _endpoint parameter in register function
  - Improved error handling for API requests
