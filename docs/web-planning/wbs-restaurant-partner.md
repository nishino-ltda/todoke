# Work Breakdown Structure: Restaurant/Partner Dashboard

## 1. Internationalization
- [x] Implement translation file structure (partner namespace added to JSON files)
- [x] Add pt-BR translations for all UI text
- [x] Create language switcher component (shared LanguageSelector in common-components)
- [x] Configure API to respect Accept-Language header
- [ ] Update database models for multilingual content

## 2. Dashboard Structure
- [x] Create protected route for partner dashboard
- [x] Implement dashboard layout with navigation
- [x] Create responsive grid for metrics widgets

## 2. Metrics Dashboard
- [x] Create Metrics Widget component (`MetricsWidget.vue`)
- [x] Display key metrics (active orders, pending deliveries)
- [ ] Add charts/graphs for visual representation
- [ ] Implement real-time updates (polling/WebSockets)

## 3. Order Management
- [x] Create Order List component (`OrderList.vue`)
- [ ] Create Order Detail component (`OrderDetail.vue`)
- [x] Display real-time order status
- [x] Implement status update functionality
- [ ] Add courier request functionality
- [ ] Implement print label functionality

## 4. Product Management
- [x] Create Product List component (`ProductList.vue`)
- [x] Create Product Form component (`ProductForm.vue`)
- [x] Implement CRUD operations for products
- [x] Add image upload capability (URL based)

## 5. Addon Management
- [x] Create Addon List component (`AddonList.vue`)
- [x] Create Addon Form component (`AddonForm.vue`)
- [x] Implement CRUD operations for addons
- [x] Link addons to products

## 6. Region/Node Management (Logistics Partners)
- [x] Create Region List component (`RegionList.vue`)
- [x] Create Region Editor component (`RegionEditor.vue`)
- [x] Create Node List component (`NodeList.vue`)
- [x] Create Node Form component (`NodeForm.vue`)
- [x] Implement CRUD operations for regions/nodes

## 7. API Integration
- [ ] Fetch partner metrics
- [ ] Fetch/update orders
- [ ] Manage products/addons
- [ ] Manage regions/nodes (for logistics partners)

## 8. Testing
- [x] Unit tests for components (PartnerDashboard, PartnerProducts, PartnerLogistics — 10 tests passing with i18n assertions)
- [ ] E2E tests for partner workflows
  - [ ] Login flow tests
  - [ ] Order management tests
