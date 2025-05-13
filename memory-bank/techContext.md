# TODOKE Technical Context

## Core Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Key Packages**:
  - Laravel Sanctum (API authentication)
  - Laravel Tinker (REPL)
  - Faker (Test data generation)

### Frontend
- **Build Tool**: Vite 6
- **File Structure**:
  - Pages organized by user role under resources/js/Pages/
  - Shared auth components in Pages/auth/
  - Customer pages in Pages/Customer/
  - Courier pages in Pages/Courier/
  - Partner pages in Pages/Partner/
  - Tests follow same directory structure
- **UI Framework**: Vuetify 3
- **HTTP Client**: Axios
- **Vue Composition API**: Using `<script setup>` syntax
  - **Conversion Patterns**:
    - **Simple Components**: Remove export default, move imports to top level
    ```vue
    <script setup>
    import { computed } from 'vue'
    const currentYear = computed(() => new Date().getFullYear())
    </script>
    ```
    - **Store Usage**: Directly use store refs without returning
    ```vue
    <script setup>
    import { useAuthStore } from '@/stores/auth'
    const authStore = useAuthStore()
    const { isAuthenticated, user } = storeToRefs(authStore)
    </script>
    ```
    - **Page Components**: Components automatically available in template
    ```vue
    <script setup>
    import GuestLayout from '@/layouts/GuestLayout.vue'
    import HomeHero from '@/components/HomeHero.vue'
    </script>
    ```
  - **Benefits**:
    - Less boilerplate
    - Better TypeScript support
    - Clearer component structure

### Testing
- PHPUnit 11
- Mockery
- Laravel Pail (log viewer)
- **Testing Patterns**:
  - Interface-based testing for database operations
  - NotificationServiceInterface pattern avoids mocking Eloquent models
  - Services depend on interfaces rather than concrete implementations
  - **Frontend Component Testing**:
    - Vue Test Utils for component testing
    - Component stubs for Vuetify components to avoid CSS import issues
    - Example pattern in HomeHero.spec.js and HomeFeatures.spec.js
    - Comprehensive assertions for component content and structure
- **Test Configuration**:
  - Process isolation enabled (prevents test interference)
  - Strict global state checking
  - SQLite in-memory database for tests
- **Test Structure**:
  - Logical grouping by feature area (Auth, Delivery, Partner, etc.)
  - Security tests organized in dedicated Security/ subdirectories
  - Updated namespace conventions (Tests\Feature\{FeatureArea})
- **Completed test standardization**:
  - All tests use `RefreshDatabase` trait
  - `Mockery::close()` added to all test `setUp` methods
  - Authentication methods updated to use direct token generation
  - 103 tests passing with reduced interdependencies
- Note: A code coverage driver is not currently configured, preventing the generation of coverage reports.

## Development Setup

### Key Commands
```bash
# Run full development environment
composer dev

# Run tests
composer test

# Frontend development
npm run dev

# Production build
npm run build
```

### Database
- SQLite for development (default)
- Migration system for schema management
- Factories for test data generation

## API Characteristics
- RESTful design
- Sanctum token authentication
- JSON responses
- Status code standardization:
  - 200: Success
  - 201: Created
  - 422: Validation error
  - 403: Forbidden

## Frontend Architecture
- Inertia.js for server-side rendered single page app experience
- Clear separation between:
  - Web routes: Handle only Inertia page rendering
  - API routes: Handle all business logic and data operations
- Frontend components make API calls to:
  - Fetch data
  - Submit forms
  - Trigger business processes
- Web routes should never contain business logic

## Deployment Considerations
- Vite-based asset compilation
- Environment variable configuration
- Queue worker for async tasks
- Database migration on deployment


## API Versioning
- All API endpoints are versioned under /api/v1/
- Frontend components must use the versioned paths
- Current API version: v1

## Known API Endpoints
- Restaurant details: GET /api/v1/restaurants/{slug}
