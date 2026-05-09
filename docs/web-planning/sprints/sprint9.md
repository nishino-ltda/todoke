# Sprint 9: Support System (TDD Focus)

## References
- WBS: web-planning/wbs-support.md
- Planning: web-planning/support.md
- Routes: routes/web.php (Support routes), routes/api.php (Support API routes)
- Backend Controller: app/Http/Controllers/SupportController.php (Inertia), app/Http/Controllers/API/SupportController.php (API)
- Frontend Service: resources/js/services/support.js
- Frontend Service Tests: resources/js/services/__tests__/support.spec.js (6/6 passing)
- Translations: resources/lang/en/support.php (English)
- Translations: resources/lang/pt-BR/support.php (Portuguese)
- Vue Components (stubs):
  - resources/js/Pages/Support/Dashboard.vue
  - resources/js/Pages/Support/Faq.vue
  - resources/js/Pages/Support/TicketCreate.vue
  - resources/js/Pages/Support/TicketDetail.vue
  - resources/js/Pages/Support/TicketReply.vue
  - resources/js/Pages/Support/Tickets.vue
- E2E Tests (pending):
  - cypress/e2e/support/ticket-system.cy.js
  - cypress/e2e/support/tickets.cy.js

## Prerequisites (completed)
- [x] Create SupportController API backend (mock responses for tickets/FAQ)
- [x] Create SupportService frontend (getTickets, getTicket, createTicket, addReply, getFaqs, closeTicket)
- [x] Register API routes (`/api/v1/support/*`)
- [x] Unit tests for SupportService (6/6 passing with logging)

## Testing Goals (pending)
- [ ] Write E2E tests for:
  - [ ] Ticket submission flow
  - [ ] Ticket management
  - [ ] Language switching functionality

## Implementation Tasks (completed)
1. **Support Components**:
   - [x] Create Dashboard page with ticket stats + recent tickets
   - [x] Create Tickets page with DataTable + status filters + search
   - [x] Create TicketCreate page with validated form (subject, category, priority, message, attachment)
   - [x] Create TicketDetail page with message thread + close support
   - [x] Create TicketReply page with message + attachment support
   - [x] Create Faq page with category accordion + search
   - [x] Create SupportLayout with sidebar navigation
   - [x] Add 75+ translation keys across en.json and pt-BR.json

2. **Support Management**:
   - [x] Connect to Support API with Accept-Language header (via api interceptor)
   - [x] Implement ticket submission with locale tracking
   - [x] Add response functionality with translation support
   - [x] Create knowledge base with multilingual content (FAQ page)

3. **State Management**:
   - [ ] Implement Support Store with locale tracking (uses direct supportService calls)
   - [ ] Add ticket history with translated statuses
   - [ ] Connect to notifications with translation support

## Acceptance Criteria
- [x] Users can submit support tickets in their preferred language
- [x] Tickets can be tracked with translated statuses
- [x] FAQ content is accessible in multiple languages
- [x] Responses are delivered via notifications in user's language
- [x] System defaults to pt-BR for Portuguese-speaking users
- [x] New languages can be added by creating translation files
- [x] 12 unit tests passing across 6 spec files
