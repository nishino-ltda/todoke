## Testing Patterns (Updated 2025-05-01)

### Frontend Testing Best Practices

1. **API Mocking**:
   - Mock axios directly rather than injecting mocks
   - Use vi.spyOn(axios, 'get|post') for precise control
   - Match production API response structure

2. **Async Handling**:
   - Always use flushPromises() after async operations
   - Await all promises before assertions
   - Test both loading and resolved states

3. **Component Stubs**:
   - Stub child components with minimal templates
   - Focus tests on component behavior
   - Avoid testing implementation details

4. **Test Organization**:
   - Group related test cases in describe blocks
   - Separate happy path and error cases
   - Keep tests focused and independent

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
