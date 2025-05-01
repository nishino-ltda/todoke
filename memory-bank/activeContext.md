# TODOKE Active Context

## Current Focus Areas
1. **Layout System Implementation**:
   - Created GuestLayout for public pages (Login, Register, Home)
   - Created AuthenticatedLayout for protected pages (Menu, Partner, etc.)
   - Implemented AppHeader with navigation and logout
   - Implemented AppFooter with copyright info
   - Updated all pages to use appropriate layouts

2. **Hybrid Delivery Implementation**:
   - Stage-based delivery tracking
   - Automatic partner assignments
   - Drone-specific status handling

3. **Community Pricing Implementation**:
   - Borda count voting system
   - Monthly voting rounds
   - Region-specific price bands
   - Automatic fare updates

4. **Product Customization**:
   - Product addons/toppings system
   - Addon-product compatibility management
   - Order customization with addons

5. **Test Improvements**:
   - Completed test standardization across all test files
   - Implemented proper mock cleanup with Mockery::close()
   - Standardized on RefreshDatabase trait for test isolation
   - Updated authentication methods in Feature tests
   - Removed incomplete test markers
   - Added assertions for delivery stages
   - Implemented automatic assignment creation
   - Testing product customization features
   - Fixed axios mocking in component tests
   - Added proper async handling with flushPromises()
   - Fixed axios mocking in component tests
   - Added proper async handling with flushPromises()

## Recent Changes
- Fixed menu.spec.js tests by:
  - Implementing direct axios mocking instead of component injection
  - Adding proper async handling with flushPromises()
  - Ensuring all API calls are properly mocked
  - Verifying both happy path and loading states

- Fixed HomeFeatures.spec.js test by:
  - Replacing direct Vuetify component imports with component stubs
  - Following the same pattern used in HomeHero.spec.js
  - Adding comprehensive test cases for content verification
  - Resolving CSS import errors in the test environment

- Fixed ProductList.spec.js test by:
  - Properly accessing component instance to emit events
  - Adding test cases for product card rendering
  - Verifying add-to-cart event propagation

## Key Decisions
1. **Testing Patterns**:
   - Direct axios mocking preferred over component injection
   - flushPromises() for async operation handling
   - Component stubs for external dependencies
   - Clear separation between unit and integration tests

2. **API Testing**:
   - Versioned endpoints (/api/v1/) for all API calls
   - Mock responses match production API structure
   - Error cases tested alongside happy paths

## Pending Tasks
- Implement E2E tests for menu browsing flow
- Add tests for cart operations
- Expand test coverage for edge cases
- Document testing patterns in systemPatterns.md

## Next Steps
1. Continue expanding test coverage for frontend components
2. Implement Cypress E2E tests for core user flows
3. Document testing best practices
  - `drone_in_route`
  - `drone_arrived`
- Updated DeliveryStatusService to:
  - Use 'delivered' as final status (replacing 'completed')
  - Preserve delivered stages during cancellations
  - Improve status validation rules
- Restructured test suite organization:
  - Grouped tests by feature area (Auth, Delivery, Partner, etc.)
  - Created dedicated Security/ subdirectories for security tests
  - Updated namespaces to match new structure (Tests\Feature\{FeatureArea})
  - Added README.md files in each Security/ directory explaining test scope
- Updated test suite:
  - Fixed HybridDeliveryEdgeCasesTest cancellation handling
  - Standardized on 'delivered' status in all tests
  - Added assertions for stage preservation
  - Fixed PartnerDeliveryTest by ensuring both stage assignments exist before status updates
  - Added tests for product addons functionality
  - Implemented node approval authorization with NodePolicy and admin middleware

## Key Decisions
1. **Layout System**:
   - GuestLayout for public pages (minimal header/footer)
   - AuthenticatedLayout for protected pages (full navigation)
   - Consistent styling across all layouts
   - Single source of truth for header/footer components

2. **Stage Management**:
   - Using JSON field for delivery stages
   - Separate assignments table for tracking
   - Strict stage ordering enforcement

3. **Status Flow**:
   - Standard statuses: pending → accepted → collected → in_transit → delivered
   - Specialized statuses for drone operations
   - Final delivery state is 'delivered' (replaces 'completed')
   - Cancellation preserves delivered stages
   - Status validation rules enforced in DeliveryStatusService

4. **Community Pricing**:
   - Borda count method for democratic voting
   - Region-specific price bands (min/avg/max fare per km)
   - Monthly voting schedule with automatic processing
   - Tie-breaking based on first-place vote counts

5. **Product Customization**:
   - Many-to-many relationship between products and addons
   - Partner-specific addons (addons belong to partners)
   - JSON storage for selected addons in order items
   - Validation of addon-product compatibility during order creation
   - Public API for viewing product addons
   - Authenticated APIs for managing addons and associations

## Pending Tasks
- Implement edge case tests for deliveries, focusing on:
  - Stage cancellations (initial implementation added)
  - Drone failures (initial implementation added)
  - Offline scenarios (currently incomplete, requires mocking) - Need to complete incomplete tests and add new tests for synchronization and conflict resolution.
- Enhance test coverage for:
  - Unit tests for services (VotingCalculationService, DeliveryStatusService, VotingRoundService, FareUpdateService) - Add assertions to existing risky tests and new tests for edge cases and scenarios.
  - Comprehensive security tests for all endpoints - Add assertions to existing risky tests and expand coverage to all sensitive endpoints and vulnerabilities.
  - Expanded performance tests with larger datasets and concurrent requests - Add assertions to existing risky tests and expand coverage for critical endpoints.
  - Tests for region validation (GeoJSON) and node management (approval flow) - Add assertions to existing risky tests and new tests for invalid GeoJSON and non-admin approval attempts.
- Investigate and configure a code coverage driver to generate coverage reports.
- Enhance community pricing with:
  - Audio forum integration
  - Cost-based pricing dashboard
  - More sophisticated price band generation
- Enhance product customization with:
  - Addon categories (e.g., sauces, toppings, extras)
  - Required vs. optional addons
  - Addon quantity limits
- Document test patterns in 02-code-style.md
- Review security tests for stage updates

## Completed Planning
- Detailed planning documents for web areas (Home, Menu, Restaurant/Partner, Courier, Admin, Support), common components, stores/services, and E2E testing strategy have been created in the `web-planning/` directory.
- Implemented layout system for all pages

## Next Steps
- Continue with frontend implementation based on the planning documents in `web-planning/`
- Develop each web area incrementally, starting with the Home page and authentication flows
- Set up the Cypress E2E testing framework and begin writing tests for core user flows
- Create test data fixtures for easier test maintenance


# Current Work (2025-05-01)

## Sprint 3 Status
✅ Completed:
- Core menu browsing functionality (ProductList, ProductCard)
- Basic cart operations (add/remove items)
- CartIcon component
- Restaurant slug routing
- API endpoint for restaurant products

⚠️ Pending:
- Implement E2E tests for product browsing and cart flow
- Product details modal with addon selection (completed)
- Checkout flow implementation

✅ Completed:
- Cart state persistence using localStorage
- @pinia/testing package installed
- Advanced cart features (persistence, item counts)

## Next Steps
1. Write Cypress E2E tests for menu browsing and cart operations
2. Develop product details modal component
3. Begin checkout flow implementation

## API Endpoint Fixes
- Updated Menu.vue to use versioned API path (/api/v1/restaurants/)
- Confirmed working restaurant endpoint at /api/v1/restaurants/{slug}
- All API routes should use the /v1/ prefix for consistency

## Next Steps
- Verify all frontend API calls use versioned endpoints
- Update API documentation in docs/03-api-routes.md
