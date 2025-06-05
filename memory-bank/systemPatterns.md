## Sprint 1 Completion Patterns (2025-05-24)

### Successful Testing Patterns
✅ 100% test coverage achieved for all components
✅ Comprehensive E2E tests for auth flows (6/6 passing)
✅ Centralized logging integrated with Cypress
✅ Pinia stores fully tested with mock implementations
✅ API service tests cover all critical paths

### Key Technical Achievements
- Hybrid authentication system working
- Translation infrastructure fully implemented
- Testing infrastructure complete
- Documentation updated across all files

## Translation Patterns (2025-05-24)

### Key Principles
1. **Separation of Concerns**:
   - Keep all UI text in translation files
   - Never hardcode strings in components
   - Store translations in JSON files (frontend) and PHP arrays (backend)
   - Follow consistent key naming (component.purpose.field)

2. **Locale Handling**:
   - Store current locale in user profile/preferences
   - Support browser language detection
   - Provide manual language selection
   - Fallback to default language (English) when translation missing

3. **Implementation**:
   - Use Vue I18n for frontend translations
   - Use Laravel's localization for backend
   - Store database content in multiple languages when needed
   - API responses respect Accept-Language header
   - Use interpolation for dynamic values ({variable} syntax)

### Testing Patterns
1. **Unit Tests**:
   - Test components with different locales
   - Verify fallback behavior
   - Check language switcher functionality
   - Mock translations in tests

2. **E2E Tests**:
   - Test UI with different language settings
   - Verify API responses respect language headers
   - Check database content in multiple languages
   - Validate form labels and error messages

### AuthForm Implementation Details
- All text replaced with translation keys
- Validation messages use translations
- Dynamic content (roles, vehicle types) translated
- Maintained 100% test coverage
- Verified through Cypress tests (6/6 passing)

## API Service Patterns (Updated 2025-05-19)

### Interceptor Implementation
1. **Request Interceptor**:
   - Sets loading state to true
   - Can modify request config
   - Must return config

2. **Response Interceptor**:
   - Sets loading state to false
   - Handles successful responses
   - Must return response

3. **Error Interceptor**:
   - Sets loading state to false
   - Handles API errors consistently
   - Returns rejected promise

### Loading State Management
- Centralized via Pinia store
- Components react to isLoading state
- Automatically managed by interceptors
- Testable via store state

### Error Handling
- Consistent error format
- Automatic loading state cleanup
- Logging of errors
- Rejection of promises for caller handling

### Testing Approach
- Mock axios instance with interceptors
- Verify loading state transitions
- Test error scenarios
- Validate interceptor order

### Example Implementation
```javascript
// Request interceptor
api.interceptors.request.use(config => {
  loadingStore.startLoading()
  return config
})

// Response interceptor
api.interceptors.response.use(
  response => {
    loadingStore.stopLoading()
    return response
  },
  error => {
    loadingStore.stopLoading()
    return Promise.reject(error)
  }
)
```

## Hybrid Authentication System (2025-05-23)

### Key Principles
1. **Dual Authentication**:
   - Uses Sanctum tokens for API calls
   - Uses web sessions for Inertia routing
   - Tokens stored in localStorage
   - Session cookie for web routes

2. **Flow**:
   - User logs in via API endpoint (/api/v1/auth/login)
   - Receives authentication token
   - Frontend converts token to web session (/api/v1/auth/token-to-session)
   - Session cookie enables Inertia routing
   - Token remains available for API calls

3. **Implementation**:
   - Backend:
     - Sanctum middleware for API routes
     - Web middleware for session routes
     - Token-to-session conversion endpoint
   - Frontend:
     - Auth service handles token conversion
     - Maintains both token and session
     - Proper error handling for both flows

4. **Security**:
   - CSRF protection for web routes
   - Token expiration handling
   - Session regeneration on login
   - Secure cookie settings

5. **Testing**:
   - Verify API token works
   - Check session creation
   - Test Inertia routing
   - Validate error cases

## Testing Patterns (Updated 2025-05-05)

### Frontend Testing Best Practices

1. **API Mocking**:
   - Mock axios directly rather than injecting mocks
   - Use vi.spyOn(axios, 'get|post') for precise control
   - Match production API response structure
   - Use versioned API paths (/api/v1/) in all tests

2. **Async Handling**:
   - Always use flushPromises() after async operations
   - For timer-based tests, use vi.runAllTimersAsync() 
   - Await all promises before assertions
   - Test both loading and resolved states
   - Use vi.useFakeTimers() in beforeEach and vi.useRealTimers() in afterEach

3. **Component Stubs**:
   - Stub child components with minimal templates
   - Focus tests on component behavior
   - Avoid testing implementation details

4. **Test Organization**:
   - Group related test cases in describe blocks
   - Separate happy path and error cases
   - Keep tests focused and independent

5. **Store Testing**:
   - Remount components when store data changes
   - Use custom mock implementations for complex verifications
   - Reset mocks between tests with mockClear()
   - For Pinia stores, use setActivePinia(createPinia()) in beforeEach
   - Mock store methods that aren't being tested

6. **Complex Data Verification**:
   - For complex objects, use custom mock implementations with inline assertions
   - Example:
   ```javascript
   mockFunction.mockImplementation((data) => {
     expect(data).toEqual({
       expectedKey: expectedValue,
       // other expected properties
     });
     return Promise.resolve({});
   });
   ```
   - This approach is more reliable than using toHaveBeenCalledWith for complex objects

7. **Cypress Logging**:
   - Always use the centralized log store (`resources/js/stores/log.js`) for logging within components and services during Cypress tests.
   - This allows Cypress tests to access and verify logged messages via the store's state or the window object, facilitating debugging and test validation.

#
## Product Display Patterns (2025-05-01)

1. **Product List**:
   - Grid layout with ProductCard components
   - Each card shows name, price and image
   - Clicking opens ProductDetailsModal

2. **Product Details Modal**:
   - Shows full product details (name, description, price, image)
   - Quantity selector (minimum 1)
   - Addon selection section (when product has addons)
   - Dynamic price calculation including selected addons
   - Add to cart button with total price
   - Emits close event when closed

3. **Addon Selection**:
   - Checkbox interface for optional addons
   - Addon prices displayed next to names
   - Selected addons stored in cart items
   - Total price updates dynamically

## Route/Controller Patterns (2025-05-17)

### Web Routes (Inertia)
- Handle page rendering via Inertia.js
- Defined in Laravel's routes/web.php
- No client-side routing (Vue-router removed)
- Key characteristics:
  - Return Inertia responses with page components
  - Can pass shared props to all pages
  - Handle server-side redirects
  - Support partial reloads via Inertia
- Example:
```php
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});
```

### API Routes
- Handle all business logic
- Defined in routes/api.php
- Return JSON responses
- Authenticated via Sanctum tokens
- Versioned under /api/v1/

### Controller Responsibilities
- Web controllers:
  - Return Inertia responses
  - No business logic
  - Can pass page-specific props
- API controllers:
  - Handle all data operations
  - Perform database operations
  - Return standardized JSON responses

### Benefits of This Approach
1. Simplified frontend routing (no Vue-router needed)
2. Clear separation of concerns
3. Better SEO with server-side rendered pages
4. Automatic handling of back/forward navigation
5. Shared data available to all pages

## Frontend Structure Patterns (2025-05-13)

### Pages Organization
1. **Directory Structure**:
   ```
   resources/js/Pages/
   ├── App.vue
   ├── auth/
   │   ├── Login.vue
   │   └── Register.vue
   ├── Customer/
   │   ├── Dashboard.vue
   │   ├── Home.vue
   │   ├── Menu.vue
   │   ├── Checkout.vue
   │   ├── Support.vue
   │   ├── Terms.vue
   │   └── Privacy.vue
   ├── Courier/
   │   └── Dashboard.vue
   └── Partner/
       ├── Dashboard.vue
       └── Admin.vue
   ```

2. **Key Principles**:
   - Pages organized by user role (Customer, Courier, Partner)
   - Shared auth components at top level
   - Each role has its own subdirectory
   - Consistent naming conventions (PascalCase for files)

3. **Routing**:
   - Routes defined in router.js with updated import paths
   - Role-specific routes prefixed in Laravel (e.g., /customer/dashboard)
   - InertiaController renders appropriate Vue components

## E2E Testing Patterns (2025-05-05)

1. **Test Logging**:
   - Use `cy.log()` extensively to document test progress
   - Particularly valuable in headless mode where UI isn't visible
   - Implement via:
     ```javascript
     cy.log('Starting test section', args)
     ```
   - For headless mode, configure task in cypress.config.js:
     ```javascript
     on("task", {
       log(args) {
         console.log(...args);
         return null;
       }
     });
     ```
   - Override cy.log for better formatting:
     ```javascript
     Cypress.Commands.overwrite("log", function(log, ...args) {
       const indent = "\t";
       const formattedArgs = args.map((arg) =>
         typeof arg === "string" ? indent + arg : indent + JSON.stringify(arg)
       );
       if (Cypress.browser.isHeadless) {
         return cy.task("log", formattedArgs, { log: false });
       } else {
         console.log(...formattedArgs);
         return log(...args);
       }
     });
     ```
   - Benefits:
     - Clear test execution flow in logs
     - Easier debugging of failed tests
     - Better visibility into test state
     - Works consistently in both headed and headless modes

2. **Test Setup**:
   - Use API interception to provide consistent test data
   - Mock API responses with fixtures
   - Visit specific pages directly rather than navigating through the app
   - Wait for API responses before proceeding with tests

2. **Element Selection**:
   - Use data-test attributes for all elements that need to be tested
   - Avoid using CSS classes or element types for selection
   - Ensure elements are properly visible before interacting with them

3. **Form Testing**:
   - Test validation errors by submitting invalid data
   - Test happy path by submitting valid data
   - Verify API requests contain the expected data
   - Test error handling for API failures

4. **API Mocking**:
   - Use cy.intercept() to mock API responses
   - Test both success and error scenarios
   - Verify request payloads with cy.wait() and assertions
   - Use fixtures for complex response data

5. **Store Testing**:
   - Use cy.getStore() custom command to access Pinia stores
   - Verify store state after actions
   - Test persistence between page reloads

6. **Error Handling**:
   - Test validation error display
   - Test network error handling
   - Verify error messages are displayed correctly
   - Ensure the application remains in a usable state after errors
