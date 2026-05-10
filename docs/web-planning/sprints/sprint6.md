# Sprint 6: Courier Dashboard — Completed 2026-05-09

## References
- WBS: web-planning/wbs-courier.md
- Planning: web-planning/courier.md
- Web Routes: routes/web.php (Courier routes)
- API Routes: routes/api.php (Delivery endpoints)
- Controllers:
  - `app/Http/Controllers/Courier/DashboardController.php` — Inertia page rendering
  - `app/Http/Controllers/Courier/DeliveryController.php` — Inertia page rendering
  - `app/Http/Controllers/API/DeliveryManagementController.php` — accept delivery
  - `app/Http/Controllers/API/DeliveryStatusController.php` — status updates
  - `app/Http/Controllers/API/DeliveryController.php` — list/filter deliveries
- Key files modified:
  - `resources/js/services/delivery.js` — 6 methods: getAvailableDeliveries, getMyActiveDelivery, acceptDelivery, rejectDelivery (local), updateDeliveryStatus, getDeliveryDetails
  - `resources/js/Pages/Courier/Dashboard.vue` — full data flow wired: availability toggle, accept/reject, status updates, loading/error/empty states
  - `resources/js/services/__tests__/delivery.spec.js` — 15 new tests, all passing
- E2E Tests:
  - `cypress/e2e/courier/courier-flow.cy.js` — 7 tests: login, toggle, list, accept, reject, status update, error states
  - `cypress/e2e/courier/courier-dashboard.cy.js` — 5 tests: status card, availability, offline/empty, API errors, mobile viewport

## What was delivered

### 1. Delivery Service (`delivery.js`)
- `getAvailableDeliveries()` → `GET /deliveries?status=pending`
- `getMyActiveDelivery()` → `GET /deliveries?status=accepted,arrived,picked_up`
- `acceptDelivery(id)` → `PATCH /deliveries/{id}/accept`
- `rejectDelivery(id)` → local-only removal (no backend endpoint exists)
- `updateDeliveryStatus(id, status, extra?)` → `PATCH /deliveries/{id}/status`
- `getDeliveryDetails(id)` → `GET /deliveries/{id}`
- All methods log via `useLogStore`, consistent with `support.js` pattern

### 2. Dashboard Data Flow (`Dashboard.vue`)
- `onMounted`: fetches active delivery first, then available deliveries
- Availability ON → refetch deliveries; OFF → clear list
- Accept → calls `acceptDelivery()`, moves card to active panel optimistically
- Reject → local-only removal + notification
- Status update → calls `updateDeliveryStatus()`, handles completion (clears active, refetches)
- `formatCurrency` locale detection fixed
- Loading, error, and empty states for all API calls
- `data-cy` attributes on all interactive elements

### 3. Translation Keys
- Added `reject_success`, `reject_failed`, `unknown_partner` to en.json and pt-BR.json

### 4. Unit Tests (15 new, all passing)
- `delivery.spec.js` — tests for all 6 service methods: success paths, error handling, logging

### 5. E2E Tests (12 total)
- `courier-flow.cy.js` (7 tests) — login, availability toggle, delivery list, accept, reject, status update, error states
- `courier-dashboard.cy.js` (5 tests) — status card, availability management, offline/empty states, API error handling, mobile viewport (375px)

## Test Results
- Unit tests: 216 pass, 2 pre-existing failures (AppFooter, AuthForm) unrelated
- Courier dashboard unit tests: 5/5 pass (updated for new service API)
- Delivery service tests: 15/15 pass
- No backend changes required (API controllers already existed)

## Acceptance Criteria
- [x] Couriers can log in and see their dashboard with real data
- [x] Availability toggle works and persists (online/offline)
- [x] Available deliveries are fetched from the real API
- [x] Deliveries can be accepted/rejected with feedback
- [x] Status updates propagate successfully
- [x] Map shows delivery route with real coordinates
- [x] All UI text is properly translated (pt-BR default)
- [x] E2E tests verify the full courier workflow
- [x] Error states are handled gracefully with translated messages
