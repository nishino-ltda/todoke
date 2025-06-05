# Work Breakdown Structure: Courier Dashboard

## 1. Dashboard Structure
- [ ] Create protected route for courier dashboard
- [ ] Implement dashboard layout with navigation
- [ ] Create responsive design for mobile use

## 2. Availability Management
- [ ] Create Availability Toggle component (`AvailabilityToggle.vue`)
- [ ] Implement online/offline status toggle
- [ ] Persist availability state

## 3. Delivery Management
- [ ] Create Available Deliveries List (`AvailableDeliveriesList.vue`)
- [ ] Display real-time available delivery requests
- [ ] Implement accept/reject functionality
- [ ] Create Assigned Delivery Details (`AssignedDeliveryDetails.vue`)
- [ ] Display delivery stages and current status
- [ ] Implement status update buttons (`StatusUpdateButtons.vue`)

## 4. Map Integration
- [ ] Create Map Display component (`DeliveryMap.vue`)
- [ ] Show current position
- [ ] Display delivery route (origin to destination)
- [ ] Implement geolocation tracking

## 5. API Integration
- [ ] Fetch available deliveries
- [ ] Handle delivery acceptance/rejection
- [ ] Update delivery statuses
- [ ] (Future) Integrate with voting endpoints

## 6. Internationalization
- [ ] Implement translation support for all UI text
- [ ] Use courier-specific translation files
- [ ] Ensure status messages are translatable
- [ ] Add language switching capability
- [ ] Test with pt-BR and English locales

## 7. Testing
- [ ] Unit tests for components
- [ ] E2E tests for courier workflows
- [ ] Test real-time updates
- [ ] Test translation functionality
