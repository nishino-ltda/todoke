# Web Area Planning: Admin Panel

## Purpose
Provide administrators with a comprehensive web interface to monitor system health, manage users, regions, and access logs.

## Internationalization Requirements
- All UI text must use translation keys from JSON files
- Default language: pt-BR (Portuguese - Brazil)
- Support for English via language switcher
- Admin-specific translations should be in `admin.json` files
- Form validation messages must be translated
- API error messages should support multiple languages

## Key Features
- Secure login for admin users.
- Dashboard displaying key system metrics (e.g., active users, deliveries in progress - leveraging `/api/v1/admin/stats`).
- User management: list, view, activate/deactivate users (leveraging `/api/v1/admin/users`, `PATCH /api/v1/admin/users/{id}/status`).
- Region management: list, view, edit regions (leveraging `/api/v1/regions`, `PUT /api/v1/regions/{id}`).
- Access to system logs (potentially linking to external logging tools like Kibana/Loki).
- Platform configuration settings (requires new API endpoints).
- Overview of all deliveries in the system.

## Implementation Considerations
- Implement highly protected routes accessible only by authenticated admin users with appropriate permissions.
- Utilize Vuetify components for layout, data tables (`v-data-table`), forms, and charts/graphs for metrics.
- Create components for dashboard widgets (`AdminStatsWidget.vue`), user list (`UserList.vue`), region list (`RegionList.vue`), and configuration forms (`SettingsForm.vue`).
- Integrate with the existing API for data retrieval and updates.
- Implement robust error handling and display for administrative actions.

## API Endpoints Used
- `/api/v1/admin/stats`
- `/api/v1/admin/users` (GET and PATCH for status updates)
- `/api/v1/regions` (GET, PUT)
- `/api/v1/deliveries` (GET for all deliveries)

## Potential Components
- Admin Dashboard Layout component (`AdminDashboardLayout.vue`)
- Admin Stats Widget component (`AdminStatsWidget.vue`)
- User List component (`UserList.vue`)
- Region List component (`RegionList.vue`)
- Settings Form component (`SettingsForm.vue`)
- Delivery List component (reusable or specific, `AdminDeliveryList.vue`)
