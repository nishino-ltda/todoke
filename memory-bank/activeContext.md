## Test Fixes (2025-05-13)

- Updated partner login tests to:
  - Use correct test credentials (partner@example.com/password123)
  - Expect Portuguese error messages from API
  - Follow actual login flow with proper redirects
- Modified auth store to redirect partners to /partner after login
- Aligned test assertions with actual API responses
