# TODOKE Active Context

## Current Focus Areas
1. **Hybrid Delivery Implementation**:
   - Stage-based delivery tracking
   - Automatic partner assignments
   - Drone-specific status handling

2. **Community Pricing Implementation**:
   - Borda count voting system
   - Monthly voting rounds
   - Region-specific price bands
   - Automatic fare updates

3. **Product Customization**:
   - Product addons/toppings system
   - Addon-product compatibility management
   - Order customization with addons

4. **Test Improvements**:
   - Completed test standardization across all test files
   - Implemented proper mock cleanup with Mockery::close()
   - Standardized on RefreshDatabase trait for test isolation
   - Updated authentication methods in Feature tests
   - Removed incomplete test markers
   - Added assertions for delivery stages
   - Implemented automatic assignment creation
   - Testing product customization features

## Recent Changes
- Fixed test interdependency issues in MenuTest:
  - Identified and resolved an issue where MenuTest passed when run individually but failed when run as part of the full test suite
  - Root cause was improper mocking of the Log facade in other tests that wasn't being cleaned up
  - Implemented a more resilient approach using direct token generation instead of relying on auth controllers
  - Added proper mock cleanup with Mockery::close() to prevent interference between tests
  - Switched from DatabaseTransactions to RefreshDatabase for cleaner test isolation
- Completed comprehensive test suite standardization:
  - Applied RefreshDatabase trait to all 26 test files (18 Feature, 8 Unit)
  - Added Mockery::close() to setUp methods for consistent mock cleanup
  - Updated authentication methods in Feature tests to use direct token generation
  - Verified all tests pass when run individually and as part of full suite
  - Resolved all test interdependencies
- Implemented product addons (toppings) functionality:
  - Created Addon model and related migrations
  - Implemented many-to-many relationship between products and addons
  - Added support for addon selection in orders
  - Created API endpoints for addon management and product-addon associations
  - Added comprehensive tests for addon functionality
- Implemented community pricing voting system:
  - Created VotingService, VotingCalculationService, VotingRoundService, and FareUpdateService
  - Added API endpoints for vote submission and retrieval
  - Implemented Borda count algorithm for vote calculation
  - Created scheduled commands for voting round management
- Added specialized drone statuses:
  - `drone_launched`
  - `drone_in_route`
  - `drone_arrived`
- Updated DeliveryStatusService to:
  - Use 'delivered' as final status (replacing 'completed')
  - Preserve delivered stages during cancellations
  - Improve status validation rules
- Updated test suite:
  - Fixed HybridDeliveryEdgeCasesTest cancellation handling
  - Standardized on 'delivered' status in all tests
  - Added assertions for stage preservation
  - Fixed PartnerDeliveryTest by ensuring both stage assignments exist before status updates
  - Added tests for product addons functionality

## Key Decisions
1. **Stage Management**:
   - Using JSON field for delivery stages
   - Separate assignments table for tracking
   - Strict stage ordering enforcement

2. **Status Flow**:
   - Standard statuses: pending → accepted → collected → in_transit → delivered
   - Specialized statuses for drone operations
   - Final delivery state is 'delivered' (replaces 'completed')
   - Cancellation preserves delivered stages
   - Status validation rules enforced in DeliveryStatusService

3. **Community Pricing**:
   - Borda count method for democratic voting
   - Region-specific price bands (min/avg/max fare per km)
   - Monthly voting schedule with automatic processing
   - Tie-breaking based on first-place vote counts

4. **Product Customization**:
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
