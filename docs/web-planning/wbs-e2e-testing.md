# Work Breakdown Structure: End-to-End Testing

## 1. Test Setup
- [ ] Install and configure Cypress
- [ ] Set up test environment
- [ ] Create test data fixtures

## 2. Authentication Tests
- [ ] Customer login/logout
- [ ] Partner login/logout
- [ ] Courier login/logout
- [ ] Admin login/logout
- [ ] Invalid credential handling

## 3. Customer Flow Tests
- [ ] Menu browsing
- [ ] Cart operations
- [ ] Checkout process
- [ ] Order placement

## 4. Partner Flow Tests
- [ ] Dashboard metrics
- [ ] Order management
- [ ] Product management
- [ ] (Logistics) Region/node management

## 5. Courier Flow Tests
- [ ] Availability toggle
- [ ] Delivery acceptance
- [ ] Status updates
- [ ] Map integration

## 6. Admin Flow Tests
- [ ] User management
- [ ] Node approval
- [ ] System monitoring
- [ ] Configuration changes

## 7. Support Flow Tests
- [ ] Ticket submission
- [ ] Ticket viewing
- [ ] (Future) Ticket communication

## 8. Translation Testing
- [ ] Verify UI text renders in correct language (default: pt-BR)
- [ ] Test language switching functionality
- [ ] Verify fallback to English when translation missing
- [ ] Test notifications with dynamic values in translations
- [ ] Check date/number formatting per locale
- [ ] Verify all tests use i18n-aware selectors
- [ ] Test with multiple language configurations
- [ ] Validate translation key consistency across tests

## 9. CI/CD Integration
- [ ] Add to build pipeline
- [ ] Configure test reporting
- [ ] Set up test artifacts
