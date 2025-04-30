# TODOKE: Community Pricing Implementation (Updated)

**Date:** April 30, 2025  
**Author:** Development Team  
**Changes:** Updated for real-time pricing and frequent voting

## Overview

Updated implementation of the community pricing system supporting:
- Voting every 30 minutes
- Real-time price calculation based on last 30 minutes of votes
- Integration with coordinate and routing APIs
- Mock services for testing while APIs are offline

## Core Changes

### 1. Data Model Updates

- **VotingRound**:
  - Added `voting_interval` field (default: 30 minutes)
  - Added `is_active` flag for real-time status
  - Removed monthly scheduling fields

- **Vote**:
  - Added `timestamp` field for real-time calculations
  - Added `delivery_type` field (motorbike/drone)

### 2. New Services

#### CoordinateService
Interface for address to coordinate conversion:
```php
interface CoordinateServiceInterface {
    public function geocode(string $address): array;
    public function reverseGeocode(float $lat, float $lon): string;
}
```

#### RoutingService 
Interface for distance calculation:
```php
interface RoutingServiceInterface {
    public function calculateRoute(
        array $origin, 
        array $destination,
        string $vehicleType
    ): array;
}
```

#### Mock Services (for testing)
```php
class MockCoordinateService implements CoordinateServiceInterface {
    // Implementation with test data
}

class MockRoutingService implements RoutingServiceInterface {
    // Implementation with test routes
}
```

### 3. Updated Services

#### VotingRoundService
- Changed from monthly to 30-minute voting rounds
- New methods:
  ```php
  public function startNewVotingRound(Region $region): VotingRound
  public function getCurrentPrice(Region $region): array
  ```

#### FareUpdateService
- Updated for real-time pricing:
  ```php
  public function calculateCurrentFare(
      Region $region, 
      string $deliveryType,
      array $route
  ): float
  ```

### 4. Real-time Pricing Algorithm

1. When a delivery is requested:
   - Get coordinates for pickup/delivery addresses
   - Calculate route distance
   - Get current price based on:
     - Votes from last 30 minutes
     - Delivery type (motorbike/drone)
     - Current demand
   - Return real-time price

2. Voting process:
   - New voting round starts every 30 minutes
   - Couriers can vote to increase/decrease prices
   - Votes affect pricing immediately for new deliveries

### 5. API Changes

New endpoints:
- `GET /api/v1/pricing/current`: Get current pricing
- `POST /api/v1/pricing/calculate`: Calculate delivery price
  ```json
  {
    "pickup_address": "123 Main St",
    "delivery_address": "456 Oak Ave", 
    "delivery_type": "motorbike"
  }
  ```

### 6. Scheduled Commands

Updated commands:
- `voting:start-rounds`: Starts new voting rounds every 30 minutes
- `voting:close-rounds`: Closes expired rounds and archives results

## Implementation Plan

1. **Database Changes**
   - Migrations for new fields
   - Indexes for performance

2. **Service Updates**
   - Modify VotingRoundService for frequent rounds
   - Update FareUpdateService for real-time pricing
   - Implement new coordinate/routing services

3. **Testing Strategy**
   - Unit tests for new pricing logic
   - Integration tests with mock services
   - Performance testing for real-time calculations

4. **Deployment**
   - Gradual rollout with feature flag
   - Monitoring for performance impact

## Mock Services Implementation

While waiting for actual APIs:

```php
class MockCoordinateService implements CoordinateServiceInterface {
    private $testAddresses = [
        '123 Main St' => ['lat' => -23.5505, 'lon' => -46.6333],
        // More test data
    ];

    public function geocode(string $address): array {
        return $this->testAddresses[$address] ?? 
            throw new AddressNotFoundException();
    }
}
```

```php
class MockRoutingService implements RoutingServiceInterface {
    public function calculateRoute(array $origin, array $destination, string $vehicleType): array {
        $distance = $this->haversineDistance($origin, $destination);
        
        return [
            'distance' => $distance,
            'duration' => $distance * ($vehicleType === 'motorbike' ? 2 : 1),
            'route' => []
        ];
    }
}
```

## Updated Workflow

1. **Voting**
   - Every 30 minutes, new voting round starts
   - Couriers vote to adjust prices up/down
   - Votes affect pricing immediately

2. **Price Calculation**
   - When delivery requested:
     - Geocode addresses
     - Calculate route
     - Apply current pricing based on recent votes
   - Return real-time price to customer

3. **Testing**
   - Use mock services while APIs are offline
   - Validate with test addresses/routes
   - Gradually replace with real APIs when available
