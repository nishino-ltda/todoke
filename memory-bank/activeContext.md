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

3. **Test Improvements**:
   - Removing incomplete test markers
   - Adding assertions for delivery stages
   - Implementing automatic assignment creation

## Recent Changes
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

## Pending Tasks
- Implement edge case tests for:
  - Stage cancellations (initial implementation added)
  - Drone failures (initial implementation added)
  - Offline scenarios (placeholder added, requires mocking)
- Enhance community pricing with:
  - Audio forum integration
  - Cost-based pricing dashboard
  - More sophisticated price band generation
- Document test patterns in 02-code-style.md
- Review security tests for stage updates
