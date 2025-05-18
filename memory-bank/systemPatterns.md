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

### Interface Usage Pattern (Updated 2025-04-29)

### Key Principles

1. **Dependency Injection**:
   - Always type-hint interfaces rather than concrete classes
   - Enables easier testing and implementation swapping
   - Particularly important for avoiding direct Eloquent model dependencies

2. **Testing**:
   - Mock interfaces rather than concrete implementations
   - Avoids the need to mock complex Eloquent model behavior
   - Makes tests more maintainable and isolated

3. **Service Layer**:
   - Define clear service contracts via interfaces
   - Hide implementation details from consumers
   - Services should depend on interfaces, not models

4. **Repository Pattern**:
   - Use interfaces for all data access
   - Makes data layer interchangeable
   - Prevents direct model usage in business logic

### Implementation Approach

1. **For Models**:
   - Create repository interfaces for model operations
   - Move all database operations to repository implementations
   - Never inject models directly into services

2. **For Notifications**:
   - Use NotificationServiceInterface
   - Implement notification sending in concrete service
   - Mock interface in tests instead of Notification model

3. **For Other Services**:
   - Define interfaces for service contracts
   - Implement concrete versions for production
   - Mock interfaces in tests

### Test Organization Pattern

1. **Logical Grouping**:
   - Tests organized by feature area (Auth, Delivery, Partner, etc.)
   - Each feature area has its own directory
   - Related tests grouped together

2. **Security Tests**:
   - Isolated in dedicated Security/ subdirectories
   - Focus on security-specific validations:
     - SQL Injection
     - XSS
     - CSRF
     - Access control
     - Security headers

3. **Namespace Conventions**:
   - Tests\Feature\{FeatureArea} for main tests
   - Tests\Feature\{FeatureArea}\Security for security tests

4. **Benefits**:
   - Clear separation of concerns
   - Easier to locate related tests
   - Better organization of security tests
   - Consistent structure across the codebase

### State Persistence Patterns

1. **LocalStorage Integration**:
   - Use Pinia's watchEffect for automatic persistence
   - Implement error handling for localStorage operations
   - JSON serialize/deserialize state
   - Load initial state from localStorage on store creation
   - Test with proper localStorage mocking

2. **Benefits**:
   - State survives page refreshes
   - No external dependencies
   - Simple implementation
   - Works across all browsers

### Benefits

- **Avoids Mocking Eloquent**:
  - No need to mock complex model relationships
  - No database setup required for unit tests
  - Tests run faster without database interactions

- **Loose Coupling**:
  - Components depend on abstractions
  - Easier to swap implementations
  - Clear separation of concerns

- **Testability**:
  - Mock interfaces are simpler than mocking models
  - Tests focus on behavior not implementation
  - More reliable test assertions

### Example Implementations

1. Repository Pattern:
```php
// Use interface in constructor
public function __construct(VotingRoundRepositoryInterface $repository) 
{
    $this->repository = $repository;
}

// Mock interface in tests
$mock = Mockery::mock(VotingRoundRepositoryInterface::class);
```

2. Notification Service:
```php
// Interface definition
interface NotificationServiceInterface {
    public function send(User $user, string $message): void;
}

// Service implementation
class NotificationService implements NotificationServiceInterface {
    public function send(User $user, string $message): void {
        $user->notifications()->create(['message' => $message]);
    }
}

// Test usage
$mock = Mockery::mock(NotificationServiceInterface::class);
$mock->shouldReceive('send')->once();
```

3. Service Layer:
```php
interface DeliveryStatusServiceInterface {
    public function update(Delivery $delivery, string $status): void;
}

// Service depends on interfaces
class DeliveryStatusService implements DeliveryStatusServiceInterface {
    public function __construct(
        private NotificationServiceInterface $notifier
    ) {}
    
    public function update(Delivery $delivery, string $status): void {
        // Update logic
        $this->notifier->send($delivery->user, "Status updated");
    }
}
```

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
