# TODOKE Product Context

## Business Purpose
TODOKE addresses the need for a fair, transparent delivery platform that:
- Democratizes pricing decisions
- Integrates traditional and drone-based delivery
- Provides equitable conditions for couriers

## User Experience

### For Couriers
1. **Pricing Participation**:
   - Monthly voting on price bands using Borda count method
   - Community audio forum for sharing local insights
   - Transparent dashboard showing cost data and demand
   - *Pricing is specifically for motoboys and can be region-specific based on factors like gas prices and demand.*

2. **Delivery Operations**:
   - Clear stage tracking for hybrid deliveries
   - Specialized status updates for drone operations
   - Automatic assignment to delivery stages

### For Customers
- Seamless ordering with hybrid delivery options
- Real-time tracking of multi-stage deliveries
- Transparent pricing based on community decisions

## Key User Flows

### Order to Delivery Flow
1. Clients browse and select products from online menus provided by restaurants and other partners on the platform.
2. The client places an Order for the selected products.
3. The relevant Restaurant or Partner receives a notification about the new Order.
4. The Restaurant or Partner prepares the items included in the Order.
5. Once the Order is ready for pickup, the system dispatches a Delivery request. This request can be assigned to either a motorcycle courier or a drone, depending on the delivery parameters and availability.

### Hybrid Delivery Flow
1. A Partner creates a hybrid delivery request, specifying the stages (e.g., motorbike to distribution center, drone from distribution center to customer).
2. System automatically:
   - Assigns motorbike courier for first stage
   - Assigns drone operator for second stage
   - Creates delivery assignments for each stage
3. Status updates propagate through stages
4. Final delivery confirmation triggers notifications

### Community Pricing Flow
1. System initiates monthly voting period
2. Couriers rank price band options
3. System calculates and implements new prices
4. Dashboard updates with new pricing data

## Social Impact
- Improves courier working conditions through:
  - Democratic pricing control
  - Transparent cost visibility
  - Fair reputation system
- Reduces environmental impact via optimized drone routing
- Increases delivery access to remote areas

## Future API Scope
- The platform's API is designed to be extensible and will not be limited to food delivery in the future.
