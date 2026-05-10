# Sprint 8: Notifications & Real-time — Completed 2026-05-09

## References
- WBS: web-planning/wbs-common-components.md, wbs-stores-services.md
- Events: `app/Events/OrderStatusChanged.php`, `DeliveryStatusChanged.php`, `NewDeliveryAvailable.php`, `NewSupportReply.php`
- Channels: `routes/channels.php`
- Echo: `resources/js/echo.js`
- Composable: `resources/js/composables/useRealtime.js`
- Component: `resources/js/Components/NotificationCenter.vue`
- Dashboards: Admin, Partner, Courier, Customer (all updated)
- E2E: `cypress/e2e/notifications/notification-system.cy.js`

## What was delivered

### 1. Backend — Laravel Reverb
- Installed `laravel/reverb` — first-party WebSocket server
- Configured `.env` with Reverb credentials, `config/broadcasting.php` with `reverb` driver
- Published/config: Reverb server config

### 2. Broadcast Events (4 events)
- `OrderStatusChanged` — broadcasts to order private channel (customer + partner)
- `DeliveryStatusChanged` — broadcasts to courier delivery channel
- `NewDeliveryAvailable` — broadcasts to all available couriers
- `NewSupportReply` — broadcasts to customer ticket channel

### 3. Channel Authorization (`routes/channels.php`)
- Private channels for orders, deliveries, couriers, support tickets
- Sanctum-compatible auth for channel access

### 4. Frontend — Laravel Echo
- `resources/js/echo.js` — Echo initialized with Reverb connector
- `resources/js/composables/useRealtime.js` — composable managing WebSocket listeners + dispatching to NotificationsStore
- `resources/js/Components/NotificationCenter.vue` — notification stack UI using AppAlert
- All 4 dashboards updated to use `useRealtime` with auto-cleanup on unmount

### 5. NotificationCenter Layout Integration
- `AuthenticatedLayout.vue` — NotificationCenter added
- `CourierLayout.vue` — NotificationCenter added
- `PartnerLayout.vue` — NotificationCenter added
- `AdminLayout.vue` — NotificationCenter added

### 6. Internationalization & Testing
- Translation keys added to en.json and pt-BR.json for all real-time alerts
- Vitest mocks updated for Inertia usePage + Echo requirements (243 unit tests passing)
- E2E test: `notification-system.cy.js` — real-time UI behavior + locale switching

### 7. Composer dev script
- Updated `composer.json` `dev` script to start Reverb alongside Vite + Laravel dev server

## Key files created/modified
- `app/Events/OrderStatusChanged.php` — new
- `app/Events/DeliveryStatusChanged.php` — new
- `app/Events/NewDeliveryAvailable.php` — new
- `app/Events/NewSupportReply.php` — new
- `routes/channels.php` — new
- `app/Providers/BroadcastServiceProvider.php` — registered
- `bootstrap/providers.php` — BroadcastServiceProvider added
- `config/broadcasting.php` — reverb driver configured
- `.env` — Reverb credentials
- `resources/js/echo.js` — new
- `resources/js/composables/useRealtime.js` — new
- `resources/js/Components/NotificationCenter.vue` — new
- `resources/js/Layouts/AuthenticatedLayout.vue` — NotificationCenter
- `resources/js/Layouts/CourierLayout.vue` — NotificationCenter
- `resources/js/Layouts/PartnerLayout.vue` — NotificationCenter
- `resources/js/Layouts/AdminLayout.vue` — NotificationCenter
- `resources/js/app.js` — Echo import
- `resources/js/stores/notification.js` — existing, used as-is
- `resources/js/Components/AppAlert.vue` — existing, used as-is
- All 4 dashboard pages — useRealtime integration
- `cypress/e2e/notifications/notification-system.cy.js` — E2E test
- `resources/lang/en.json`, `resources/lang/pt-BR.json` — notification keys
- `composer.json` — dev script with Reverb

## Acceptance Criteria
- [x] Notifications display correctly in all supported languages for all roles
- [x] Real-time updates work via Laravel Reverb WebSocket
- [x] Notification history maintained with translations
- [x] Notification content externalized to translation files
- [x] E2E tests verify notification behavior for all user roles
- [x] `composer dev` starts Reverb automatically
