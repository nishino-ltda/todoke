# Sprint 9: Support System E2E (TDD Focus)

## References
- WBS: web-planning/wbs-support.md
- Planning: web-planning/support.md
- Routes: routes/web.php (Support routes), routes/api.php (Support API routes)
- Backend Controller: `app/Http/Controllers/API/SupportController.php` (mock responses)
- Frontend Service: `resources/js/services/support.js` (getTickets, getTicket, createTicket, addReply, getFaqs, closeTicket)
- Frontend Service Tests: `resources/js/services/__tests__/support.spec.js` (6/6 passing)
- Vue Components (already built and tested):
  - `resources/js/Pages/Support/Dashboard.vue` — ticket stats + recent tickets
  - `resources/js/Pages/Support/Faq.vue` — category accordion + search
  - `resources/js/Pages/Support/TicketCreate.vue` — validated form
  - `resources/js/Pages/Support/TicketDetail.vue` — message thread + close
  - `resources/js/Pages/Support/TicketReply.vue` — reply + attachment
  - `resources/js/Pages/Support/Tickets.vue` — DataTable + status filters + search
- Unit Tests (all passing):
  - `resources/js/Pages/Support/__tests__/Dashboard.spec.js`
  - `resources/js/Pages/Support/__tests__/Faq.spec.js`
  - `resources/js/Pages/Support/__tests__/TicketCreate.spec.js`
  - `resources/js/Pages/Support/__tests__/TicketDetail.spec.js`
  - `resources/js/Pages/Support/__tests__/TicketReply.spec.js`
  - `resources/js/Pages/Support/__tests__/Tickets.spec.js`
- Layout: `resources/js/Layouts/SupportLayout.vue` (sidebar navigation)
- E2E Test Files (placeholders):
  - `cypress/e2e/support/support_flow.cy.js`
  - `cypress/e2e/support/ticket-system.cy.js`
  - `cypress/e2e/support/tickets.cy.js`

## Prerequisites (completed)
- [x] All support page components built and unit tested (12 tests, 6 spec files)
- [x] SupportService frontend with 6 methods (6/6 unit tests passing)
- [x] SupportController API backend with mock responses
- [x] API routes registered (`/api/v1/support/*`)
- [x] SupportLayout with sidebar navigation
- [x] 75+ translation keys across en.json and pt-BR.json
- [x] Accept-Language header handling via global API interceptor

## Testing Goals
- [ ] **E2E tests for support flows** (replace 3 placeholders with real logic):
  - [ ] Ticket submission flow (create ticket, validate form, success/error)
  - [ ] Ticket management (list, filter by status, search, pagination)
  - [ ] Ticket detail + reply (view thread, add reply, close ticket)
  - [ ] FAQ browsing (search, category accordion)
  - [ ] Language switching on support pages
  - [ ] Error states (API failures, validation errors)

## Implementation Tasks
1. **E2E Tests**:
   - Write real test logic in 3 support E2E files:
     - `cypress/e2e/support/support_flow.cy.js` — end-to-end support workflow: login → dashboard → browse FAQ → create ticket → view ticket → add reply → close
     - `cypress/e2e/support/ticket-system.cy.js` — ticket lifecycle: create with all fields, validation errors, view in list, open details, reply, close
     - `cypress/e2e/support/tickets.cy.js` — ticket management: list display, status filter buttons, search, pagination, empty state
   - Use `cy.intercept()` to mock support API responses
   - Test with pt-BR locale (default)
   - Include error/empty state scenarios
   - Use `data-cy` selectors
   - Add `cy.log()` with emoji for test visibility

2. **Support Store** (if missing):
   - [ ] Create Support Store (`resources/js/stores/support.js`) with locale tracking
   - [ ] Add ticket history with translated statuses
   - [ ] Add unit tests

## Acceptance Criteria
- [ ] Users can submit support tickets with form validation
- [ ] Tickets display in list with status filters and search
- [ ] Ticket detail page shows message thread with reply functionality
- [ ] Tickets can be closed from detail view
- [ ] FAQ page allows browsing by category and searching
- [ ] All UI text is properly translated (pt-BR default)
- [ ] E2E tests verify all support flows end-to-end
- [ ] Error states are handled gracefully with translated messages

## Known Test Status
- Baseline: 49 test files, 238 tests, all passing (as of Sprint 7 completion)
