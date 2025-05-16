# Work Breakdown Structure: Restaurant/Partner Dashboard

## 1. Dashboard Structure
- [ ] Create protected route for partner dashboard
- [ ] Implement dashboard layout with navigation
- [ ] Create responsive grid for metrics widgets

## 2. Metrics Dashboard
- [ ] Create Metrics Widget component (`MetricsWidget.vue`)
- [ ] Display key metrics (active orders, pending deliveries)
- [ ] Add charts/graphs for visual representation
- [ ] Implement real-time updates (polling/WebSockets)

## 3. Order Management
- [ ] Create Order List component (`OrderList.vue`)
- [ ] Create Order Detail component (`OrderDetail.vue`)
- [ ] Display real-time order status
- [ ] Implement status update functionality
- [ ] Add courier request functionality
- [ ] Implement print label functionality

## 4. Product Management
- [ ] Create Product List component (`ProductList.vue`)
- [ ] Create Product Form component (`ProductForm.vue`)
- [ ] Implement CRUD operations for products
- [ ] Add image upload capability

## 5. Addon Management
- [ ] Create Addon List component (`AddonList.vue`)
- [ ] Create Addon Form component (`AddonForm.vue`)
- [ ] Implement CRUD operations for addons
- [ ] Link addons to products

## 6. Region/Node Management (Logistics Partners)
- [ ] Create Region List component (`RegionList.vue`)
- [ ] Create Region Editor component (`RegionEditor.vue`)
- [ ] Create Node List component (`NodeList.vue`)
- [ ] Create Node Form component (`NodeForm.vue`)
- [ ] Implement CRUD operations for regions/nodes

## 7. API Integration
- [ ] Fetch partner metrics
- [ ] Fetch/update orders
- [ ] Manage products/addons
- [ ] Manage regions/nodes (for logistics partners)

## 8. Testing
- [ ] Unit tests for components
- [ ] E2E tests for partner workflows
  - [ ] Login flow tests
  - [ ] Order management tests
