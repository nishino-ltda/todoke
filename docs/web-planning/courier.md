# Web Area Planning: Courier Dashboard

## Purpose
Provide couriers with a web interface to manage their availability, view and accept delivery assignments, and update delivery statuses.

## Key Features
- Secure login for courier users.
- Toggle online/offline status to indicate availability for deliveries.
- Real-time display of available delivery requests (polling `/api/v1/deliveries` with appropriate filters).
- Ability to accept or reject delivery requests (`PATCH /api/v1/deliveries/{id}/accept`).
- View details of assigned deliveries, including origin, destination, item details, and stages for hybrid deliveries.
- Update delivery status as they progress through the delivery lifecycle (e.g., collected, in transit, delivered - leveraging `PATCH /api/v1/deliveries/{id}/status`).
- Display current position on a map and potentially the delivery route.
- (Potential Future) Access to community pricing voting interface (`POST /api/v1/voting/vote`, `GET /api/v1/voting/active`) and audio forum.

## Implementation Considerations
- Implement protected routes accessible only by authenticated courier users.
- Utilize Vuetify components for layout, lists, buttons, and status indicators.
- Implement real-time updates for available deliveries and status changes using polling (`setInterval`).
- Create components for status toggle (`AvailabilityToggle.vue`), available deliveries list (`AvailableDeliveriesList.vue`), assigned delivery details (`AssignedDeliveryDetails.vue`), and status update buttons (`StatusUpdateButtons.vue`).
- Integrate with the existing API for fetching data and updating statuses.
- Integrate a map component (e.g., Leaflet or Google Maps API) to display locations and routes.

## API Endpoints Used
- `/api/v1/deliveries` (GET for available deliveries)
- `/api/v1/deliveries/{id}/accept` (PATCH)
- `/api/v1/deliveries/{id}/status` (PATCH)
- `/api/v1/voting/vote` (POST - potential future)
- `/api/v1/voting/active` (GET - potential future)

## Potential Components
- Availability Toggle component (`AvailabilityToggle.vue`)
- Available Deliveries List component (`AvailableDeliveriesList.vue`)
- Assigned Delivery Details component (`AssignedDeliveryDetails.vue`)
- Status Update Buttons component (`StatusUpdateButtons.vue`)
- Map Display component (`DeliveryMap.vue`)
