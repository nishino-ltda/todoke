# TODOKE: Community Pricing Implementation

**Date:** April 28, 2025  
**Author:** Development Team

## Overview

This document describes the implementation of the community pricing system in TODOKE, which allows couriers to democratically determine delivery prices through a voting mechanism. The system uses the Borda count method for vote calculation and supports region-specific pricing.

## Core Components

### 1. Data Models

- **VotingRound**: Represents a voting period with start/end times, status, and region
  - Fields: `id`, `start_time`, `end_time`, `status` (active/closed), `region_id`
  - Relationships: belongs to Region, has many VotingOptions, has many Votes

- **VotingOption**: Represents price band options within a voting round
  - Fields: `id`, `voting_round_id`, `min_fare_per_km`, `avg_fare_per_km`, `max_fare_per_km`
  - Relationships: belongs to VotingRound

- **Vote**: Stores user votes with ranked options
  - Fields: `id`, `voting_round_id`, `user_id`, `ranked_options` (JSON array of option IDs)
  - Relationships: belongs to VotingRound, belongs to User

### 2. Services

#### VotingService

Handles vote submission and retrieval of active voting rounds.

Key methods:
- `submitVote(votingRoundId, rankedOptions, userId)`: Validates and stores a user's vote
- `getActiveVotingRound(regionId)`: Gets the active voting round for a specific region
- `getAllActiveVotingRounds()`: Gets all active voting rounds across all regions

#### VotingCalculationService

Implements the Borda count algorithm for calculating voting results.

Key methods:
- `calculateResults(votingRoundId)`: Calculates voting results using Borda count
- `handleTieBreak(results)`: Resolves ties based on first-place votes
- `getWinningOption(results)`: Returns the winning option from calculated results

#### VotingRoundService

Manages voting rounds, including creation, scheduling, and closing.

Key methods:
- `createVotingRound(regionId, startTime, endTime, options)`: Creates a new voting round
- `scheduleMonthlyVotingRounds()`: Creates voting rounds for all regions
- `closeVotingRound(votingRoundId, calculationService, fareUpdateService)`: Closes a round and processes results
- `processExpiredVotingRounds(calculationService, fareUpdateService)`: Processes all expired voting rounds

#### FareUpdateService

Updates region pricing based on voting results.

Key methods:
- `updateRegionPricing(regionId, minFarePerKm, avgFarePerKm, maxFarePerKm)`: Updates a region's pricing
- `calculateDeliveryFare(regionId, distanceKm, timeOfDay, demandFactor)`: Calculates fare for a delivery

### 3. API Endpoints

- `POST /api/v1/voting/vote`: Submit a vote for an active voting round
  - Request: `{ "voting_round_id": 1, "ranked_options": [3, 1, 2] }`
  - Response: `{ "message": "Vote submitted successfully", "vote": {...} }`

- `GET /api/v1/voting/active`: Get active voting rounds
  - Optional query param: `region_id`
  - Response: `{ "voting_rounds": [...] }` or `{ "voting_round": {...} }`

- `GET /api/v1/voting/results/{roundId}`: Get results for a specific voting round
  - Response: `{ "voting_round": {...}, "results": {...}, "winning_option": {...} }`

### 4. Scheduled Commands

- `voting:schedule-rounds`: Creates monthly voting rounds for all regions
  - Scheduled to run on the 1st day of each month

- `voting:process-expired`: Processes expired voting rounds and updates pricing
  - Scheduled to run daily

## Borda Count Algorithm

The Borda count method assigns points to options based on their ranking:
- 1st place: n points (where n is the number of options)
- 2nd place: n-1 points
- 3rd place: n-2 points
- And so on...

The option with the highest total points wins. In case of a tie, the option with more first-place votes wins.

### Example

Given 3 options (A, B, C) and 5 voters with the following rankings:
- Voter 1: A > B > C
- Voter 2: B > A > C
- Voter 3: B > C > A
- Voter 4: C > A > B
- Voter 5: A > C > B

Points calculation:
- Option A: (3 + 2 + 1 + 2 + 3) = 11 points
- Option B: (2 + 3 + 3 + 1 + 1) = 10 points
- Option C: (1 + 1 + 2 + 3 + 2) = 9 points

Option A wins with 11 points.

## Workflow

1. **Voting Round Creation**
   - System creates monthly voting rounds for each region
   - Each round has multiple price band options
   - Status starts as "active" during the voting period

2. **Voting Process**
   - Couriers are notified when a voting round begins
   - They access the voting interface through the app
   - They rank the available price band options
   - System stores their ranked preferences

3. **Result Calculation**
   - When voting period ends, system calculates results using Borda count
   - Each option gets points based on its ranking position
   - Higher rankings receive more points
   - Option with highest total points wins

4. **Price Implementation**
   - Winning price bands are applied to the respective region
   - New deliveries use updated pricing
   - Dashboard shows current and historical pricing data

## Future Enhancements

1. **Audio Forum Integration**
   - Allow couriers to share information via audio messages
   - Organize messages by region and topic
   - Highlight relevant information for voting decisions

2. **Cost-Based Pricing Dashboard**
   - Visualize cost data (fuel prices, demand, etc.)
   - Show historical pricing trends
   - Provide simulators for testing different pricing scenarios

3. **Advanced Price Band Generation**
   - Use historical data to generate more relevant price band options
   - Consider seasonal factors and regional differences
   - Incorporate external data sources (fuel prices, traffic conditions)

## Testing

The implementation includes comprehensive tests:
- Feature tests for API endpoints
- Unit tests for the Borda count algorithm
- Integration tests for the complete voting flow

Run the tests with:
```bash
php artisan test --filter=CommunityPricingTest
