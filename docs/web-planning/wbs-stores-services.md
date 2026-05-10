# Work Breakdown Structure: Stores & Services

## 1. State Management (Pinia Stores)
- [x] Implement Auth Store (7/7 tests passing)
  - [x] Manage authentication state
  - [x] Handle login/logout
  - [x] Persist session
  - [x] Unit tests (100% coverage)
  - [x] Auto-login for all user types (no more pending approval)
  - [x] `setAuth()` stores `all_roles` from user response
  - [x] `register()` always calls token-to-session and sets auth
- [x] Implement Cart Store
  - [x] Manage shopping cart state
  - [x] Handle cart operations
  - [x] Persist cart
- [x] Implement User Data Store
  - [x] Manage user profile data
  - [x] Handle profile updates
- [x] Implement Notifications Store
  - [x] Manage UI notifications
  - [x] Handle notification display
- [x] Implement Loading Store
  - [x] Track loading states
  - [x] Manage loading indicators

## 2. Frontend Services
- [x] Implement API Service
  - [x] Configure Axios instance
  - [x] Set up request/response interceptors
  - [x] Handle error responses
- [x] Implement Auth Service
  - [x] Handle login/registration
  - [x] Manage tokens
  - [x] Unit tests (8/8 passing, 100% coverage)
- [x] Implement User Service
  - [x] Fetch/update user data
- [x] Implement Delivery Service
  - [x] Handle delivery operations
- [x] Implement Partner Service
  - [x] Handle partner operations
- [x] Implement Admin Service
  - [x] Handle admin operations
- [x] Implement Support Service
  - [x] Handle support tickets (getTickets, getTicket, createTicket, addReply, getFaqs)
  - [x] Unit tests (6/6 passing)
  - [x] Backend controller with mock responses
- [x] Implement Map Service
  - [x] Handle geocoding (geocode, reverseGeocode, getDistance)
  - [x] Unit tests (4/4 passing)
  - [x] Backend controller with mock responses

## 3. Testing
- [x] Unit tests for stores (Auth, Loading)
- [x] Unit tests for Auth Service
- [x] Unit tests for Support Service (6/6 passing)
- [x] Unit tests for Map Service (4/4 passing)
- [ ] Unit tests for other services
- [ ] Integration tests with components
- [x] E2E testing setup (6/6 auth tests passing)

## 4. Internationalization
- [x] Implement Translation Store:
  - [x] Persist language selection in localStorage
  - [x] Load translation files dynamically
  - [x] Provide translation lookup methods
  - [x] Handle fallback to default language
  - [x] Unit tests for all functionality
- [x] Implement Translation Service:
  - [x] Handle language switching
  - [x] Provide translation utilities
  - [x] Support pluralization
  - [x] Support interpolation
  - [x] Unit tests for all functionality
- [x] Add language support to Auth Store:
  - [x] Store user language preference
  - [x] Sync with user profile
  - [x] Handle language selection events
  - [x] Unit tests for all functionality
- [x] Update API Service:
  - [x] Add Accept-Language header to all requests
  - [x] Handle language-specific error messages
  - [x] Support localized API responses
  - [x] Unit tests for all functionality
