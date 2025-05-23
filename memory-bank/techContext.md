# TODOKE Technical Context

## Core Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Key Packages**:
  - Production:
    - Laravel Framework (^12.0)
    - Laravel Sanctum (^4.0) - API authentication
    - Laravel Tinker (^2.10.1) - REPL
    - InertiaJS Laravel (^2.0) - Frontend integration
    - Tightenco Ziggy (^2.0) - Route generation
  - Development:
    - FakerPHP (^1.23) - Test data generation
    - Laravel Breeze (^2.3) - Authentication scaffolding
    - Laravel Pail (^1.2.2) - Log viewer
    - Laravel Pint (^1.13) - Code style
    - Mockery (^1.6) - Mocking library
    - PHPUnit (^11.5.3) - Testing framework   
  

### Frontend
- **Build Tool**: Vite 6 (^6.2.4)
- **Core Libraries**:
  - Vue 3 (^3.4.0)
  - Pinia (^3.0.2) - State management
  - Vuetify 3 (^3.8.0-beta.0) - UI framework
  - Axios (^1.8.2) - HTTP client
  - MDI Font (^7.4.47) - Icons
- **Dev Dependencies**:
  - InertiaJS Vue3 (^2.0.0)
  - Vue Test Utils (^2.4.6)
  - Vitest (^3.1.3) - Testing
  - Cypress (^14.3.3) - E2E testing
  - Vite plugins (Vue, Laravel)
- **File Structure**:
  - Pages organized by user role under resources/js/Pages/
  - Shared auth components in Pages/auth/
  - Customer pages in Pages/Customer/
  - Courier pages in Pages/Courier/
  - Partner pages in Pages/Partner/
  - Tests follow same directory structure
- **UI Framework**: Vuetify 3
  - **Component Usage**:
    - All components use Vuetify 3 syntax
    - Buttons use `v-btn` with proper props
    - Forms use `v-form` with validation
    - Layout uses `v-row` and `v-col` for grid
  - **Inertia Integration**:
    - Links use Inertia's `Link` component
    ```vue
    <Link :href="route('login')" class="text-primary" data-test="login-link">
      Already have an account?
    </Link>
    ```
    - Avoid `to=` attribute (router-link syntax)
    - Use `route()` helper for named routes
- **HTTP Client**: Axios
  - **Interceptor Implementation**:
    - Request interceptor:
      ```javascript
      api.interceptors.request.use(config => {
        loadingStore.startLoading()
        return config
      })
      ```
    - Response interceptor:
      ```javascript
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
    - **Key Features**:
      - Automatic loading state management
      - Centralized error handling
      - Tested with 100% coverage
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
    import GuestLayout from '@/Layouts/GuestLayout.vue'
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
  - **Logging in Tests**:
    - Use log store (resources/js/stores/log.js) instead of console.log
    - Logs available to Cypress via window.logStore API
    - Includes timestamps and message history
    - Access logs in tests:
    ```javascript
    cy.window().then(win => {
      const logs = win.logStore.getLogs();
    });
    ```
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

## Internationalization Implementation
- **Translation Files**:
  - Stored in `resources/lang/{locale}/` directories
  - JSON format for frontend translations
  - PHP arrays for backend translations
  - pt-BR as first supported language
  - Easy to add new languages by creating new locale directories

- **Vue Implementation**:
  - Vue I18n library for frontend translations
  - Language switcher component
  - Automatic locale detection from browser/user preferences
  - Fallback to English if translation missing

- **Laravel Implementation**:
  - Built-in localization features
  - Translation strings in PHP arrays
  - Locale middleware for API responses
  - Database content can be stored in multiple languages

## Frontend Architecture
- **Inertia.js**:
  - Version: 2.0.9
  - Configuration:
    - Properly initialized in resources/js/app.js
    - Uses `createInertiaApp` with page component resolver
    - All controllers return Inertia responses
  - Routing:
    - Web routes handle page rendering (defined in Laravel routes/web.php)
    - API routes handle business logic (defined in routes/api.php)
    - Frontend uses Inertia's `Link` component for navigation (replaces Vue-router)
    - No client-side routing needed - all navigation handled server-side via Laravel
  - Key Benefits:
    - Single-page app experience without client-side routing complexity
    - Full page reloads when needed (preserving scroll position)
    - Automatic handling of back/forward navigation
    - Shared data via Inertia's shared props
  - Testing:
    - Components include `data-test` attributes
    ```vue
    <v-btn data-test="submit-button">Submit</v-btn>
    ```
    - Test navigation using Inertia's `router.visit()` mock
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
