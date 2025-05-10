# Web Area Planning: Restaurant/Partner Dashboard

## Purpose
Provide partners (restaurants, logistics companies) with a web interface to manage orders, products, and potentially regions and nodes.

## Key Features
- Secure login for partner users.
- Dashboard overview displaying key metrics (active orders, pending deliveries, etc. - leveraging `/api/v1/partner/metrics`).
- Real-time list of current orders with status (polling `/api/v1/orders` or using WebSockets).
- Ability to update order statuses (e.g., accepted, preparing, awaiting delivery - leveraging `PATCH /api/v1/orders/{id}/status`).
- Option to print customer address labels (requires a separate printing service or client-side generation).
- Functionality to request a courier or drone for pickup (requires integration with delivery allocation logic, potentially a new API endpoint).
- Product and addon management: list, add, edit, and remove (leveraging `/api/v1/products`, `POST /api/v1/products`, `PUT /api/v1/products/{product}`, `/api/v1/addons`, `POST /api/v1/addons`, `PUT /api/v1/addons/{addon}`, `DELETE /api/v1/addons/{addon}`, `POST /api/v1/products/{product}/addons`).
- (For Logistics Partners) Region management: view, create, edit regions (leveraging `/api/v1/regions`, `POST /api/v1/regions`, `PUT /api/v1/regions/{id}`).
- (For Logistics Partners) Node management: view, add, edit nodes (leveraging `/api/v1/nodes`, `POST /api/v1/nodes`).
- (Potential Future) Simple stock management.

## Implementation Considerations
- Implement protected routes accessible only by authenticated partner users.
- Utilize Vuetify components for layout, data tables (`v-data-table`), forms, and modals.
- Implement real-time updates for orders using polling (`setInterval`) or explore WebSocket integration for a more efficient solution.
- Create components for dashboard widgets (`MetricsWidget.vue`), order list (`OrderList.vue`), order details (`OrderDetail.vue`), product/addon forms (`ProductForm.vue`, `AddonForm.vue`), region editor (`RegionEditor.vue`), and node management (`NodeList.vue`, `NodeForm.vue`).
- Integrate with the existing API for all data interactions.
- Consider permissions within the partner role (e.g., only logistics partners can manage regions/nodes).

## API Endpoints Used
- `/api/v1/partner/metrics`
- `/api/v1/orders` (GET and PATCH for status updates)
- `/api/v1/products` (GET, POST, PUT)
- `/api/v1/products/{product}/addons` (POST)
- `/api/v1/addons` (GET, POST, PUT, DELETE)
- `/api/v1/regions` (GET, POST, PUT)
- `/api/v1/nodes` (GET, POST)

## Potential Components
- Dashboard Layout component (`PartnerDashboardLayout.vue`)
- Metrics Widget component (`MetricsWidget.vue`)
- Order List component (`OrderList.vue`)
- Order Detail component (`OrderDetail.vue`)
- Product List component (`ProductList.vue`)
- Product Form component (`ProductForm.vue`)
- Addon List component (`AddonList.vue`)
- Addon Form component (`AddonForm.vue`)
- Region List component (`RegionList.vue`)
- Region Editor component (`RegionEditor.vue`)
- Node List component (`NodeList.vue`)
- Node Form component (`NodeForm.vue`)
