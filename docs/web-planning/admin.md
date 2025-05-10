# Web Area Planning: Admin Panel

## Purpose
Provide administrators with a comprehensive web interface to monitor system health, manage users, nodes, regions, and access logs.

## Key Features
- Secure login for admin users.
- Dashboard displaying key system metrics (e.g., active users, deliveries in progress, node status - leveraging `/api/v1/admin/stats`).
- User management: list, view, activate/deactivate users (leveraging `/api/v1/admin/users`, `PATCH /api/v1/admin/users/{id}/status`).
- Node management: list, view, approve/reject pending nodes (leveraging `/api/v1/nodes`, `PATCH /api/v1/admin/nodes/{node}/approve`).
- Region management: list, view, edit regions (leveraging `/api/v1/regions`, `PUT /api/v1/regions/{id}`).
- Access to system logs (potentially linking to external logging tools like Kibana/Loki).
- Platform configuration settings (requires new API endpoints).
- Overview of all deliveries in the system.

## Implementation Considerations
- Implement highly protected routes accessible only by authenticated admin users with appropriate permissions.
- Utilize Vuetify components for layout, data tables (`v-data-table`), forms, and charts/graphs for metrics.
- Create components for dashboard widgets (`AdminStatsWidget.vue`), user list (`UserList.vue`), node list (`NodeList.vue`), region list (`RegionList.vue`), and configuration forms (`SettingsForm.vue`).
- Integrate with the existing API for data retrieval and updates.
- Implement robust error handling and display for administrative actions.

## API Endpoints Used
- `/api/v1/admin/stats`
- `/api/v1/admin/users` (GET and PATCH for status updates)
- `/api/v1/nodes` (GET)
- `/api/v1/admin/nodes/{node}/approve` (PATCH)
- `/api/v1/regions` (GET, PUT)
- `/api/v1/deliveries` (GET for all deliveries)

## Potential Components
- Admin Dashboard Layout component (`AdminDashboardLayout.vue`)
- Admin Stats Widget component (`AdminStatsWidget.vue`)
- User List component (`UserList.vue`)
- Node List component (`NodeList.vue`)
- Region List component (`RegionList.vue`)
- Settings Form component (`SettingsForm.vue`)
- Delivery List component (reusable or specific, `AdminDeliveryList.vue`)
