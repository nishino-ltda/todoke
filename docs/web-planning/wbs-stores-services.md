# Work Breakdown Structure: Stores & Services

## 1. State Management (Pinia Stores)
- [x] Implement Auth Store
  - [x] Manage authentication state
  - [x] Handle login/logout
  - [x] Persist session
- [ ] Implement Cart Store
  - [ ] Manage shopping cart state
  - [ ] Handle cart operations
  - [ ] Persist cart
- [ ] Implement User Data Store
  - [ ] Manage user profile data
  - [ ] Handle profile updates
- [ ] Implement Notifications Store
  - [ ] Manage UI notifications
  - [ ] Handle notification display
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
- [ ] Implement User Service
  - [ ] Fetch/update user data
- [ ] Implement Delivery Service
  - [ ] Handle delivery operations
- [ ] Implement Partner Service
  - [ ] Handle partner operations
- [ ] Implement Admin Service
  - [ ] Handle admin operations
- [ ] Implement Support Service
  - [ ] Handle support tickets
- [ ] Implement Map Service
  - [ ] Handle geocoding
  - [ ] Manage map interactions

## 3. Testing
- [x] Unit tests for stores (Auth, Loading)
- [x] Unit tests for Auth Service
- [ ] Unit tests for other services
- [ ] Integration tests with components
- [ ] E2E testing setup

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
