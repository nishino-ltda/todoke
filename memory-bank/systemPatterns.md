## Interface Usage Pattern (Updated 2025-04-29)

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
