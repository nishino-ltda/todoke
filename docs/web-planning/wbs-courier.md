# Work Breakdown Structure: Courier Dashboard

## 1. Dashboard Structure
- [x] Create protected route for courier dashboard
- [x] Implement dashboard layout with navigation
- [x] Create responsive design for mobile use

## 2. Availability Management
- [x] Create Availability Toggle component (`AvailabilityToggle.vue`)
- [x] Implement online/offline status toggle
- [x] Persist availability state

## 3. Delivery Management
- [x] Create Available Deliveries List (`AvailableDeliveriesList.vue`)
- [x] Display real-time available delivery requests
- [x] Implement accept/reject functionality
- [x] Create Assigned Delivery Details (`AssignedDeliveryDetails.vue`)
- [x] Display delivery stages and current status
- [x] Implement status update buttons (`StatusUpdateButtons.vue`)

## 4. Map Integration
- [ ] Create Map Display component (`DeliveryMap.vue`)

  *[DEPENDENCY: MapService (services/map.js) — done]*
- [ ] Show current position
- [ ] Display delivery route (origin to destination)
- [ ] Implement geolocation tracking

## 5. API Integration
- [x] Create MapService backend/API controller (`API/MapController.php`) with geocode, reverseGeocode, distance
- [x] Register map API routes (`/api/v1/map/*`)
- [ ] Fetch available deliveries
- [ ] Handle delivery acceptance/rejection
- [ ] Update delivery statuses
- [ ] (Future) Integrate with voting endpoints

## 6. Internationalization
- [x] Implement translation support for all UI text
- [x] Use courier-specific translation keys in shared files
- [x] Ensure status messages are translatable
- [x] Add language switching capability (shared LanguageSelector)
- [x] Test with pt-BR and English locales

## 7. Testing
- [x] Unit tests for components (CourierDashboard — 4 tests passing with i18n assertions)
- [ ] E2E tests for courier workflows
- [ ] Test real-time updates
- [x] Test translation functionality
